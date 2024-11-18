<?php

namespace jobs;

interface JobInterface {

    /**
     * Executes the job.
     *
     * @return mixed
     */
    public function exec();

}