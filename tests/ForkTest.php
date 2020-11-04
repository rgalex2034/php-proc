<?php

require_once __DIR__."/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use Nyugodt\Proc;

class ForkTest extends TestCase{

    /**
     * @test
     */
    public function join(){
        $fork = new Proc\Fork(function(){
            sleep(1);
        });
        $fork->fork()->join();
        $this->assertTrue($fork->getExitStatus() == 0, "Error exit status.");
    }

    /**
     * @test
     */
    public function exec(){
        $fork = Proc\Fork::exec("/usr/bin/env", ["sleep", "1"])->join();
        $this->assertTrue($fork->getExitStatus() == 0, "Error exit status.");
    }

    /**
     * @test
     */
    public function wait(){
        Proc\Fork::exec("/usr/bin/env", ["sleep", "1"]);
        Proc\Fork::exec("/usr/bin/env", ["sleep", "1"]);
        Proc\Fork::exec("/usr/bin/env", ["sleep", "1"]);
        while($child = Proc\Fork::wait()){
            $this->assertTrue($child->getExitStatus() == 0, "Error exit status.");
        }
    }

}
