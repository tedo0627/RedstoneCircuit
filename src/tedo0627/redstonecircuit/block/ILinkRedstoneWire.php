<?php

namespace tedo0627\redstonecircuit\block;

interface ILinkRedstoneWire {

    public function isConnect(int $face): bool;
}