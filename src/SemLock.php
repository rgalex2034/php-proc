<?php

namespace Nyugodt\Proc;

/**
 *  This class allow the use of lock at a file level.
 *  When creating a lock, a path to a file must be provided.
 *  If no path is provided, the lock will act on the caller file path.
 */
class SemLock{

    private static $locks;

    private $key;
    private $sem_id;

    /**
     * Create a new Lock for a specific file.
     * If path is not provider, the caller file path will be used.
     * @param string $path - A valid file path.
     */
    public function __construct(string $path = null){
        //Get the parent caller file path
        $path = $path ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]["file"];
        $this->key = ftok($path, "C");
        $this->sem_id = sem_get($this->key);
        if(!$this->sem_id) throw new Exception("Unable to get semaphore: {$this->key}");
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
        return isset(self::$locks[$this->key]);
    }

    /**
     * Execute a callable inside the semaphore.
     * Lock it before execution, and release after it.
     * @param callable $function - A callable function/string or object.
     */
    public function synchronize(callable $function, ...$args){
        if(!$this->insideLock() && !sem_acquire($this->sem_id)) throw new Exception("Unable to acquire lock for IPC key {$this->key}");
        $first_call = !$this->insideLock();
        self::$locks[$this->key] = true;
        try{
            return $function(...$args);
        }catch(Exception $e){
            throw $e;
        }finally{
            if($first_call){
                unset(self::$locks[$this->key]);
                sem_release($this->sem_id);
            }
        }
    }

}
