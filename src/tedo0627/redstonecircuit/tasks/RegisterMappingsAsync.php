<?php

namespace tedo0627\redstonecircuit\tasks;

use pocketmine\scheduler\AsyncTask;
use tedo0627\redstonecircuit\RedstoneCircuit;

class RegisterMappingsAsync extends AsyncTask {


    public function onRun(): void {
        RedstoneCircuit::registerMappings();
    }
}