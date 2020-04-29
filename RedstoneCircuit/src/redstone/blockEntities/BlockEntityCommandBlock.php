<?php

namespace redstone\blockEntities;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\CommandSender;

use pocketmine\command\utils\InvalidCommandSyntaxException;

use pocketmine\inventory\InventoryHolder;

use pocketmine\lang\TextContainer;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginException;

use pocketmine\tile\Container;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\Nameable;
use pocketmine\tile\NameableTrait;
use pocketmine\tile\Spawnable;

use pocketmine\timings\Timings;

use pocketmine\utils\TextFormat;


use redstone\blocks\BlockCommand;
use redstone\blocks\BlockCommandChain;
use redstone\blocks\BlockCommandRepeating;

use redstone\selector\CommandSelector;

use redstone\inventories\CommandInventory;

use function array_merge;
use function count;
use function is_string;
use function preg_match_all;
use function spl_object_hash;
use function stripslashes;
use function strlen;
use function strval;
use function substr;

class BlockEntityCommandBlock extends Spawnable implements InventoryHolder, Container, Nameable, CommandSender {
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    protected $inventory;
    
    protected $commandBlockMode = 0;
    protected $command = "";
    protected $lastOutput = "";
    protected $auto = false;
    protected $conditionalMode = false;
    protected $powered = false;
    
    protected function readSaveData(CompoundTag $nbt) : void {
        $this->inventory = new CommandInventory($this);

        if ($nbt->hasTag("commandBlockMode")) {
            $this->commandBlockMode = $nbt->getInt("commandBlockMode");
        }
        if ($nbt->hasTag("command")) {
            $this->command = $nbt->getString("command");
        }
        if ($nbt->hasTag("lastOutput")) {
            $this->lastOutput = $nbt->getString("lastOutput");
        }
        if ($nbt->hasTag("auto")) {
            $this->auto = $nbt->getByte("auto") == 1;
        }
        if ($nbt->hasTag("conditionalMode")) {
            $this->conditionalMode = $nbt->getByte("conditionalMode") == 1;
        }
        if ($nbt->hasTag("powered")) {
            $this->powered = $nbt->getByte("powered") == 1;
        }

        $this->loadName($nbt);

        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setInt("commandBlockMode", $this->commandBlockMode);
        $nbt->setString("command", $this->command);
        $nbt->setString("lastOutput", $this->lastOutput);
        $nbt->setByte("auto", $this->auto ? 1 : 0);
        $nbt->setByte("conditionalMode", $this->conditionalMode ? 1 : 0);
        $nbt->setByte("powered", $this->powered ? 1 : 0);

        $this->saveName($nbt);
    }

    public function getDefaultName() : string {
        return "CommandBlock";
    }
    
    public function getInventory(){
        return $this->inventory;
    }

    public function getRealInventory(){
        return $this->inventory;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
        $nbt->setInt("commandBlockMode", $this->getCommandBlockMode());
        $nbt->setString("Command", $this->getCommand());
        $nbt->setString("LastOutput", $this->getLastOutput());
        $nbt->setByte("auto", $this->isAuto() ? 1 : 0);
        $nbt->setByte("conditionalMode", $this->isConditionalMode() ? 1 : 0);
        $nbt->setByte("powered", $this->isPowered() ? 1 : 0);
        
        $this->addNameSpawnData($nbt);
    }
    
    public function onUpdate() : bool {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->isRepeating()) {
            $block = $this->getBlock();
            if (!($block instanceof BlockCommand)) {
                $this->close();
            }

            if (!$this->isPowered() && !$this->isAuto()) {
                return true;
            }

            if ($this->getCommand() == "") {
                return true;
            }

            $this->dispatch();
        }
        return true;
    }
    
    public function dispatch() : void {
        $block = $this->getBlock();
        if (!($block instanceof BlockCommand)) {
            $this->close();
        }

        if (!$this->isPowered() && !$this->isAuto()) {
            return;
        }

        $args = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $this->getCommand(), $matches);
        foreach($matches[0] as $k => $_){
            for($i = 1; $i <= 2; ++$i){
                if($matches[$i][$k] !== ""){
                    $args[$k] = stripslashes($matches[$i][$k]);
                    break;
                }
            }
        }
        $sentCommandLabel = "";
        $target = $this->getServer()->getCommandMap()->matchCommand($sentCommandLabel, $args);

        if($target === null){
            $this->sendMessage($this->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));
            return;
        }

        $target->timings->startTiming();

        for ($i = 0; $i < count($args); ++$i) {
            $str = $args[$i];
            if (strlen($str) == 0) {
                continue;
            }

            if (substr($str, 0, 1) != "@") {
                continue;
            }

            $selector = new CommandSelector();
            $entities = $selector->getEntities($this, $str);
            if (count($entities) == 0) {
                continue;
            }

            $args[$i] = $entities;
        }

        $commands = $this->getSelectorCommand($sentCommandLabel, $args);
        $conditions = false;
        try {
            foreach ($commands as $command) {
                $bool = $target->execute($this, $sentCommandLabel, $command);
                if ($bool) {
                    $conditions = true;
                }
            }
        }catch(InvalidCommandSyntaxException $e){
            $this->sendMessage($this->getServer()->getLanguage()->translateString("commands.generic.usage", [$target->getUsage()]));
        }finally{
            $target->timings->stopTiming();
        }

        $damage = $block->getDamage();
        $block = $block->getSide($damage > 8 ? $damage - 8 : $damage);
        if (!($block instanceof BlockCommandChain)) {
            return;
        }

        $tile = $block->getBlockEntity();
        if ($tile->isConditionalMode() && !$conditions) {
            return;
        }

        $tile->dispatch();
        return;
    }

    private function getSelectorCommand(string $label, array $args) : array {
        $array = [];
        $check = false;
        for ($i = 0; $i < count($args); ++$i) {
            $arg = $args[$i];
            if (is_string($arg)) {
                continue;
            }

            foreach ($arg as $entity) {
                $copy = $args;
                $copy[$i] = $entity instanceof Player ? $entity->getName() : strval($entity->getId());
                $array = array_merge($array, $this->getSelectorCommand($label, $copy));
                $check = true;
            }
        }

        if (!$check) {
            $array[] = $args;
        }

        return $array;
    }

    public function isNormal() : bool {
        return $this->getCommandBlockMode() == 0;
    }

    public function isRepeating() : bool {
        return $this->getCommandBlockMode() == 1;
    }

    public function isChain() : bool {
        return $this->getCommandBlockMode() == 2;
    }
    
    public function getCommandBlockMode() : int {
        return $this->commandBlockMode;
    }

    public function setCommandBlockMode(int $mode) : void {
        if ($this->commandBlockMode != $mode) {
            $damage = $this->getBlock()->getDamage();
            if ($mode == 0) {
                $this->level->setBlock($this, new BlockCommand($damage));
            } elseif ($mode == 1) {
                $this->level->setBlock($this, new BlockCommandRepeating($damage));
            } elseif ($mode == 2) {
                $this->level->setBlock($this, new BlockCommandChain($damage));
            }
            $this->commandBlockMode = $mode;
        }
    }

    public function getCommand() : string {
        return $this->command;
    }

    public function setCommand(string $command) : void {
        if ($this->command != $command) {
            $this->command = $command;
            $this->onChanged();
        }
    }

    public function getLastOutput() : string {
        return $this->lastOutput;
    }

    public function setLastOutput(string $out) : void {
        if ($this->lastOutput != $out) {
            $this->lastOutput = $out;
            $this->onChanged();
        }
    }

    public function isAuto() : bool {
        return $this->auto;
    }

    public function setAuto(bool $auto) : void {
        if ($this->auto != $auto) {
            $this->auto = $auto;
            $this->onChanged();
        }
    }

    public function isConditionalMode() : bool {
        return $this->conditionalMode;
    }

    public function setConditionalMode(bool $mode) : void {
        if ($this->conditionalMode != $mode) {
            $this->conditionalMode = $mode;

            $block = $this->getBlock();
            $block->setDamage($block->getDamage() ^ 0x08);
            $this->level->setBlock($this, $block, true);
            $this->onChanged();
        }
    }
    
    public function isPowered() : bool {
        return $this->powered;
    }

    public function setPowered(bool $power) : void {
        $this->powered = $power;
    }

    // interface method
    
    private $attachments = [];

    private $permissions = [];
    

    public function sendMessage($message) {
        if($message instanceof TextContainer){
            $message = $this->getServer()->getLanguage()->translate($message);
        }else{
            $message = $this->getServer()->getLanguage()->translateString($message);
        }
        $this->setLastOutput($message);
    }

    public function getServer() {
        return Server::getInstance();
    }

    public function getScreenLineHeight() : int {
        return 1;
    }

    public function setScreenLineHeight(int $height = null) {

    }

    public function isOp() : bool{
        return true;
    }

    public function setOp(bool $value){

    }
    
    public function isPermissionSet($name) : bool{
        return isset($this->permissions[$name instanceof Permission ? $name->getName() : $name]);
    }

    public function hasPermission($name) : bool{
        if($name instanceof Permission){
            $name = $name->getName();
        }

        if($this->isPermissionSet($name)){
            return $this->permissions[$name]->getValue();
        }

        if(($perm = PermissionManager::getInstance()->getPermission($name)) !== null){
            $perm = $perm->getDefault();

            return $perm === Permission::DEFAULT_TRUE or ($this->isOp() and $perm === Permission::DEFAULT_OP) or (!$this->isOp() and $perm === Permission::DEFAULT_NOT_OP);
        }else{
            return Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_TRUE or ($this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_OP) or (!$this->isOp() and Permission::$DEFAULT_PERMISSION === Permission::DEFAULT_NOT_OP);
        }

    }

    public function addAttachment(Plugin $plugin, string $name = null, bool $value = null) : PermissionAttachment{
        if(!$plugin->isEnabled()){
            throw new PluginException("Plugin " . $plugin->getDescription()->getName() . " is disabled");
        }

        $result = new PermissionAttachment($plugin, $this->parent ?? $this);
        $this->attachments[spl_object_hash($result)] = $result;
        if($name !== null and $value !== null){
            $result->setPermission($name, $value);
        }

        $this->recalculatePermissions();

        return $result;
    }

    public function removeAttachment(PermissionAttachment $attachment){
        if(isset($this->attachments[spl_object_hash($attachment)])){
            unset($this->attachments[spl_object_hash($attachment)]);
            if(($ex = $attachment->getRemovalCallback()) !== null){
                $ex->attachmentRemoved($attachment);
            }

            $this->recalculatePermissions();

        }

    }

    public function recalculatePermissions(){
        Timings::$permissibleCalculationTimer->startTiming();

        $this->clearPermissions();
        $permManager = PermissionManager::getInstance();
        $defaults = $permManager->getDefaultPermissions($this->isOp());
        $permManager->subscribeToDefaultPerms($this->isOp(), $this->parent ?? $this);

        foreach($defaults as $perm){
            $name = $perm->getName();
            $this->permissions[$name] = new PermissionAttachmentInfo($this->parent ?? $this, $name, null, true);
            $permManager->subscribeToPermission($name, $this->parent ?? $this);
            $this->calculateChildPermissions($perm->getChildren(), false, null);
        }

        foreach($this->attachments as $attachment){
            $this->calculateChildPermissions($attachment->getPermissions(), false, $attachment);
        }

        Timings::$permissibleCalculationTimer->stopTiming();
    }

    public function clearPermissions(){
        $permManager = PermissionManager::getInstance();
        $permManager->unsubscribeFromAllPermissions($this->parent ?? $this);

        $permManager->unsubscribeFromDefaultPerms(false, $this->parent ?? $this);
        $permManager->unsubscribeFromDefaultPerms(true, $this->parent ?? $this);

        $this->permissions = [];
    }
    
    private function calculateChildPermissions(array $children, bool $invert, ?PermissionAttachment $attachment){
        $permManager = PermissionManager::getInstance();
        foreach($children as $name => $v){
            $perm = $permManager->getPermission($name);
            $value = ($v xor $invert);
            $this->permissions[$name] = new PermissionAttachmentInfo($this->parent ?? $this, $name, $attachment, $value);
            $permManager->subscribeToPermission($name, $this->parent ?? $this);

            if($perm instanceof Permission){
                $this->calculateChildPermissions($perm->getChildren(), !$value, $attachment);
            }
        }
    }

    public function getEffectivePermissions() : array{
        return $this->permissions;
    }
}