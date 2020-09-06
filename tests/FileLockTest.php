<?php

require_once __DIR__."/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use ARG\Proc;

final class FileLockTest extends TestCase{

    public function __construct(){
        parent::__construct();
    }

    /**
     * @test
     */
    public function instantiation(){
        $lock = Proc\FileLock::newInstace();
        $this->assertInstanceOf(Proc\FileLock::class, $lock);
    }

}
