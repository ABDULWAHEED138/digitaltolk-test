<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = null;
       $authUser= $request->__authenticatedUser->user_type;
        if($userId = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($userId);
        }

        if($authUser == config('services.admin_role_id') || $authUser == config('services.super_admin_id'))
        {
            $response = $this->repository->getAll($request);
        }
        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $response = $this->repository->store($request->__authenticatedUser, $request->all());

        return response($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $response = $this->repository->updateJob($id, $request->except(['_token', 'submit']), $request->__authenticatedUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $response = $this->repository->storeJobEmail($request->all());

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if(!$request->get('user_id')){
            return null;
        }
        $userId = $request->get('user_id');

        $response = $this->repository->getUsersJobsHistory($userId, $request);
        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $response = $this->repository->acceptJob($request->all(), $request->__authenticatedUser);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $response = $this->repository->acceptJobWithId($request->get('job_id'), $request->__authenticatedUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $response = $this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $response = $this->repository->endJob($request->all());

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $response = $this->repository->customerNotCall($request->all());

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $response = $this->repository->getPotentialJobs($request->__authenticatedUser);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = "";
        $time = "";
        $jobId = "";
        $session = "";
        $flagged = 'no';
        $manuallyHandled = 'no';
        $byAdmin = 'no';
        $adminComment = "";

        if (isset($data['distance']) && $data['distance'] != "") {
            $distance = $data['distance'];
        }

        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        }

        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobId = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        }

        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        }
        
        if ($data['manually_handled'] == 'true') {
            $manuallyHandled = 'yes';
        }

        if ($data['by_admin'] == 'true') {
            $byAdmin = 'yes';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $adminComment = $data['admincomment'];
        }

        if ($time || $distance) {
            Distance::where('job_id', '=', $jobId)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($adminComment || $session || $flagged || $manuallyHandled || $byAdmin) {
            Job::where('id', '=', $jobId)->update(array('admin_comments' => $adminComment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manuallyHandled, 'by_admin' => $byAdmin));
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $response = $this->repository->reopen($request->all());

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->jobid);
        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $jobData, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $job = $this->repository->find($request->jobid);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
