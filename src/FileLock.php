<?php

namespace Nyugodt\Proc;

/**
 *  This class allow the use of lock at a file level.
 *  When creating a lock, a path to a file must be provided.
 *  If no path is provided, the lock will act on the caller file path.
 */
class FileLock{

    static private $locks = [];

    private $path;
    private $handle;

    /**
     * Create a new Lock for a specific file.
     * If path is not provider, the caller file path will be used.
     * @param string $path - A valid file path.
     */
    public function __construct(string $path = null){
        //Get the parent caller file path
        $path = $path ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]["file"].".lock";
        $this->path = $path;
        $this->handle = fopen($path, "w");
        if(!$this->handle) throw new Exception("Unable to open file: {$this->path}");
    }

    /**
     * Create a new Lock for a specific file.
     * If path is not provider, the caller file path will be used.
     * @param string $path - A valid file path.
     */
    public static function newInstance(string $path = null): self{
        return new self($path);
    }

    private function insideLock(): bool{
        return isset(self::$locks[$this->path]);
    }

    /**
     * Execute a callable inside the semaphore.
     * Lock it before execution, and release after it.
     * @param callable $function - A callable function/string or object.
     */
    public function synchronize(callable $function, ...$args){
        if(!$this->insideLock() && !flock($this->handle, LOCK_EX)) throw new Exception("Unable to acquire lock for path {$this->path}");
        $firt_call = !$this->insideLock(); //After acquiring the lock, if we are not "inside" a lock, asume we are the first call
        self::$locks[$this->path] = true;
        try{
            return $function(...$args);
        }catch(Exception $e){
            throw $e;
        }finally{
            if($firt_call){
                unset(self::$locks[$this->path]);
                flock($this->handle, LOCK_UN);
            }
        }
    }

    public function __destruct(){
        fclose($this->handle);
    }

}
