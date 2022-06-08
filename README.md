[![](https://poggit.pmmp.io/shield.state/RedstoneCircuit)](https://poggit.pmmp.io/p/RedstoneCircuit)
[![](https://poggit.pmmp.io/shield.dl.total/RedstoneCircuit)](https://poggit.pmmp.io/p/RedstoneCircuit)
[![](https://img.shields.io/github/downloads/tedo0627/RedstoneCircuit/total)]()
<h1>RedstoneCircuit<img width=100 align="left" src="https://github.com/tedo0627/RedstoneCircuit/blob/master/icon.png?raw=true"></h1>

This is the PocketMine plugin that implements the Redstone circuit.

## Implemented Blocks
<details>
  <summary>
    transmission
  </summary>
  <ul>
    <li>Redstone Comparator
    <li>Redstone Repeater
    <li>Redstone Wire
  </ul>
</details>
<details>
  <summary>
    power
  </summary>
  <ul>
    <li>Buttons
    <li>Daylight Sensor
    <li>Juke Box
    <li>Lever
    <li>Observer
    <li>Redstone Block
    <li>Redstone Torch
    <li>Pressure Plates
    <li>Target
    <li>Trapped Chest
    <li>Tripwire
    <li>Tripwire Hook
  </ul>
</details>
<details>
  <summary>
    mechanism
  </summary>
  <ul>
    <li>Activator Rail
    <li>Command Block
    <li>Dispenser
    <li>Doors
    <li>Dragon Skull
    <li>Dropper
    <li>Fence Gates
    <li>Hopper
    <li>Moving Block
    <li>Note Block
    <li>Piston
    <li>Piston Arm
    <li>Powered Rail
    <li>Redstone Lamp
    <li>Sticky Piston
    <li>Sticky Piston Arm
    <li>TNT
    <li>Trapdoors
  </ul>
</details>

## Custom Events
When using custom events, they must be enabled in the configuration.
|name|description|
|:-------|:-----|
|BlockDispenseEvent|Called when the item is dispense|
|BlockPistonExtendEvent|Called when the piston is extended|
|BlockPistonRetractEvent|Called when the piston retracted|
|BlockRedstonePowerUpdateEvent|Called when the redstone signal is turned on or off|
|BlockRedstoneSignalUpdateEvent|Called when the redstone power changes|
|HopperMoveItemEvent|Called when the hopper moves an item|
|HopperPickupItemEvent|Called when the hopper picks up an item|

## License
"Redstone Circuit" is under [GPL-3.0 License](https://github.com/tedo0627/RedstoneCircuit/blob/master/LICENSE)

## Special Thanks
Write Icon [@num4nua](https://twitter.com/num4nua)
