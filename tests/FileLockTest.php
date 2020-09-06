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

    /**
     * @test
     */
    public function synchronization(){
        Proc\FileLock::newInstace()->synchronize(function(){
            $this->assertTrue(true, "Nothing went wrong.\n");
        });
    }

    /**
     * @test
     */
    public function nestedSynchronization(){
        try{
            $lock = Proc\FileLock::newInstace();
            $lock->synchronize(function() use($lock){
                $lock->synchronize(function(){
                    $this->assertTrue(true, "Nothing went wrong.\n");
                });
            });
        }catch(Exception $e){
            $this->assertFalse(true, $e->getMessage());
        }
    }

}
