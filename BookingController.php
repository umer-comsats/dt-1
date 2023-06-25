<?php

namespace DTApi\Http\Controllers;

use DTApi\Repository\BookingRepository;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $repository;

    const ADMIN_ROLE_ID = 1; // Assuming Role ID for ADMIN
    const SUPERADMIN_ROLE_ID = 2; // Assuming Role ID for SUPERADMIN

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function index(Request $request)
    {
        $userId = $request->get('user_id');
        $userRole = $request->__authenticatedUser->user_type;

        if ($userId) {
            $response = $this->repository->getUsersJobs($userId);
        } elseif ($userRole == self::ADMIN_ROLE_ID || $userRole == self::SUPERADMIN_ROLE_ID) {
            $response = $this->repository->getAll($request);
        }

        return response($response ?? []);
    }

    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job ?? []);
    }

    public function store(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->store($request->__authenticatedUser, $request->all());

        return response($response);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->updateJob($id, $request->except(['_token', 'submit']), $request->__authenticatedUser);

        return response($response);
    }

    public function immediateJobEmail(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->storeJobEmail($request->all());

        return response($response);
    }

    public function getHistory(Request $request)
    {
        $userId = $request->get('user_id');
        
        if ($userId) {
            $response = $this->repository->getUsersJobsHistory($userId, $request);

            return response($response);
        }

        return null;
    }

    public function acceptJob(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->acceptJob($request->all(), $request->__authenticatedUser);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $request->validate([
            'job_id' => 'required', // assuming job_id is required
        ]);

        $response = $this->repository->acceptJobWithId($request->get('job_id'), $request->__authenticatedUser);

        return response($response);
    }

    public function cancelJob(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser);

        return response($response);
    }

    public function endJob(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->endJob($request->all());

        return response($response);
    }

    public function customerNotCall(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->customerNotCall($request->all());

        return response($response);
    }

    public function getPotentialJobs(Request $request)
    {
        $response = $this->repository->getPotentialJobs($request->__authenticatedUser);

        return response($response);
    }

    // Distance Feed method would need a more complex validation because of the various fields involved.
    // Since we don't know exactly what the fields should be, this is an assumed validation. 
    public function distanceFeed(Request $request)
    {
        $request->validate([
            'distance' => 'string|nullable',
            'time' => 'string|nullable',
            'jobid' => 'required',
            'session_time' => 'string|nullable',
            'flagged' => 'boolean',
            'admincomment' => 'string|required_if:flagged,true',
            'manually_handled' => 'boolean',
            'by_admin' => 'boolean',
        ]);

        $data = $request->all();

        $flagged = $data['flagged'] ? 'yes' : 'no';
        $manuallyHandled = $data['manually_handled'] ? 'yes' : 'no';
        $byAdmin = $data['by_admin'] ? 'yes' : 'no';

        if ($request->hasAny(['distance', 'time'])) {
            Distance::where('job_id', $data['jobid'])
                ->update([
                    'distance' => $data['distance'] ?? "",
                    'time' => $data['time'] ?? ""
                ]);
        }

        if ($request->hasAny(['admincomment', 'session_time', 'flagged', 'manually_handled', 'by_admin'])) {
            Job::where('id', $data['jobid'])
                ->update([
                    'admin_comments' => $data['admincomment'] ?? "",
                    'flagged' => $flagged,
                    'session_time' => $data['session_time'] ?? "",
                    'manually_handled' => $manuallyHandled,
                    'by_admin' => $byAdmin
                ]);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $request->validate([
            // validation rules go here
        ]);

        $response = $this->repository->reopen($request->all());

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $request->validate([
            'jobid' => 'required', // assuming job_id is required
        ]);

        $job = $this->repository->find($request->get('jobid'));
        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $jobData, '*');

        return response(['success' => 'Push sent']);
    }

    public function resendSMSNotifications(Request $request)
    {
        $request->validate([
            'jobid' => 'required', // assuming job_id is required
        ]);

        $job = $this->repository->find($request->get('jobid'));
        $jobData = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()]);
        }
    }
}
