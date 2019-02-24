<?php

namespace redstone\blocks;

interface IRedstone {

    function getStrongPower(int $face) : int;

    function getWeakPower(int $face) : int;

    function isPowerSource() : bool;

    function onRedstoneUpdate() : void;
}