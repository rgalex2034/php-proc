<?php

require_once __DIR__."/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use Nyugodt\Proc;

final class SemLockTest extends TestCase{

    public function __construct(){
        parent::__construct();
    }

    /**
     * @test
     */
    public function instantiation(){
        $lock = Proc\SemLock::newInstance();
        $this->assertInstanceOf(Proc\SemLock::class, $lock);
    }

    /**
     * @test
     */
    public function synchronization(){
        Proc\SemLock::newInstance()->synchronize(function(){
            $this->assertTrue(true, "Nothing went wrong.\n");
        });
    }

    /**
     * @test
     */
    public function nestedSynchronization(){
        try{
            $lock = Proc\SemLock::newInstance();
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
