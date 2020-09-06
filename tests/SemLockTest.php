<?php

require_once __DIR__."/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use ARG\Proc;

final class SemLockTest extends TestCase{

    public function __construct(){
        parent::__construct();
    }

    /**
     * @test
     */
    public function instantiation(){
        $lock = Proc\SemLock::newInstace();
        $this->assertInstanceOf(Proc\SemLock::class, $lock);
    }

}
