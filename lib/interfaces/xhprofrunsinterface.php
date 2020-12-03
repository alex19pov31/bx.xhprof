<?php

namespace Bx\XHProf\Interfaces;

interface XHProfRunsInterface
{
    /**
     * Returns XHProf data given a run id ($run) of a given
     * type ($type).
     *
     * Also, a brief description of the run is returned via the
     * $run_desc out parameter.
     * @param $runId
     * @param $type
     * @param $runDesc
     */
    public function get($runId, $type, &$runDesc);

    /**
     * Save XHProf data for a profiler run of specified type
     * ($type).
     *
     * The caller may optionally pass in run_id (which they
     * promise to be unique). If a run_id is not passed in,
     * the implementation of this method must generated a
     * unique run id for this saved XHProf run.
     *
     * Returns the run id for the saved XHProf run.
     * @param $xhprofData
     * @param $type
     * @param null $runId
     */
    public function save($xhprofData, $type, $runId = null);

    /**
     * @param $runId
     * @param $type
     * @return bool
     */
    public function delete($runId, $type): bool;
}
