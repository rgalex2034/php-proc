<?php

namespace Nyugodt\Proc;

/**
 * Wrapper class for pcntl functions related to process forking.
 * Allows execution of callables on another thread or execution of commands.
 * Does not share any memory space. Any communication must be done externally
 * either by explicit shared memory or other means of communication (like files or connections)
 */
class Fork{

    private static $childs = [];

    private $pid;
    private $func;
    private $status;

    /**
     * @param callable $func - A callable function, object, closure, function name or array. https://www.php.net/manual/en/language.types.callable.php
     */
    public function __construct(callable $func){
        $this->func = $func;
    }

    /**
     * Execute a command in a separate thread.
     * This is the same as forking a process and calling pcntl_exec() to substitute the current running process.
     * This function forks once called, there is no need to call ->fork().
     * @param string $cmd - An executable path
     * @param array $args - An array of arguments passed to $cmd
     * @param array $end - An indexed by key array that substitutes the environment variables.
     * @return self - A Fork object with the current process.
     */
    public static function exec(string $cmd, array $args = [], array $env = []): self{
        $child = new self(function() use($cmd, $args, $env){
            $env ?
                pcntl_exec($cmd, $args, $env) :
                pcntl_exec($cmd, $args);
        });
        $child->fork();
        return $child;
    }

    /**
     * Fork this process and execute the current callable.
     * @return self - Returns itself.
     */
    public function fork(): self{
        if($this->pid > 0) throw new Exception("Fork already running.");
        $pid = pcntl_fork();
        if($pid > 0){
            //Fork created successfully, we are executing the parent.
            $this->pid = $pid;
            self::$childs[$pid] = $this;
        }else if($pid == 0){
            //Fork created successfully, we are executing the child.
            $this->pid = getmypid();
            ($this->func)($this);
            exit(0);
        }else{
            //Unable to fork process
            $err_code = pcntl_get_last_error();
            $err_msg  = pcntl_strerror($err_code);
            throw new Exception("Can't fork process. Error $err_code: $err_msg");
        }

        return $this;
    }

    /**
     * Wait unit this process ends.
     * @throws Exception - When trying to join a process from itself.
     * @return self - Returns itself.
     */
    public function join(): self{
        if($this->getPid() == getmypid()) throw new Exception("Trying to join child from the forked process. This method can only be executed by it's parent.");
        $pid = pcntl_waitpid($this->pid, $status);
        $this->status = $status;
        unset(self::$childs[$this->pid]);
        return $this;
    }

    /**
     * Wait for any process forked with this class to end.
     * @return self|null - Returns the Fork object that ended. Null if no more childs are running.
     */
    public static function wait(): ?self{
        do{
            //Do not wait for childs not invoked without this class.
            if(empty(self::$childs)) return null;

            $pid = pcntl_wait($status);

            //If no childs found, or error, return null.
            if($pid <= 0){
                return null;
            }

            //If childs is found to be forked from this class, return it, otherwise wait more.
            if(isset(self::$childs[$pid])){
                //Grab found child and set status
                $child = self::$childs[$pid];
                $child->status = $status;

                //Unset and return finished child
                unset(self::$childs[$pid]);
                return $child;
            }

        }while(true);
    }

    /**
     * Returns the exit code from the process. By convention, 0 is successful, any other number is an error.
     * @throws Exception - When trying to grab the status from a child that has not exited.
     * @return int - The exit code from this process.
     */
    public function getExitStatus(): int{
        if(is_null($this->status)) throw new Exception("Process did not end. Must call ->join() before grabbing exit status.");
        return pcntl_wexitstatus($this->status);
    }

    /**
     * Gets the pid from this process.
     * @return int - Current process id of this child.
     */
    public function getPid(): int{
        if(!$this->pid) throw new Exception("Process not forked. Must call ->fork() before obtaining the PID.");
        return $this->pid;
    }

}
