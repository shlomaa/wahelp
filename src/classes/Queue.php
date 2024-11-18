<?php

namespace classes;

use jobs\JobInterface;

class Queue {

    public static function push(JobInterface $job, $timeout = 0) {
        // Write to query.
    }

    /**
     * PHP daemon to process queue.
     * When fails with exception, returns to queue.
     * Otherwise pass.
     */
    public function listen() {
        while(true) {
            if ($item = $this->pull()) {
                try {
                    $this->process($item);
                    $this->delete($item);
                }
                catch (\Exception) {
                    $this->release($item);
                }
            }
        }
    }

    /**
     * Get from queue and delete in queue.
     * returns JobInterface $job
     */
    public function pull() {
        // Write to query.

        return $job;
    }

    /**
     * Add back to queue with timeout.
     *
     * @param JobInterface $job
     */
    public function release(JobInterface $job) {
        $this->push($job, 60);
    }

    /**
     * Process item.
     *
     * @param JobInterface $job
     * @return mixed
     */
    public function process(JobInterface $job) {


        return $job->execute();
    }

    /**
     * Delete item from queue.
     *
     * @param JobInterface $job
     */
    public function delete(JobInterface $job) {
        // delete from queue.
    }
}
