<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\Department;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\EventRole;

use App\Events\EventChanged;
use Carbon\Carbon;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('bindings');
    }

    // Private function to manage file uploads
    private function handleUpload($request)
    {
        $fileName = false;

        // Save event image with a unique name
        if($request->hasFile('image'))
        {
            // Create upload folder if it doesn't exist
            if(!file_exists(public_path() . '/files/event'))
            {
                mkdir(public_path() . '/files/event', 0755, true);
            }

            // Make sure the original filename is sanitized
            $file = pathinfo($request->file('image')->getClientOriginalName());
            $fileName = preg_replace('/[^a-z0-9-_]/i', '', $file['filename']) . "." . preg_replace('/[^a-z0-9-_]/i', '', $file['extension']);

            // Move file to uploads directory
            $fileName = time() . '-' . $fileName;
            $request->file('image')->move(public_path() . '/files/event', $fileName);
        }

        return $fileName;
    }

    // Display event creation page
    public function createForm(Request $request)
    {
        $this->authorize('create-event');
        return view('pages/event/create');
    }

    // Create a new event
    public function create(EventRequest $request)
    {
        $this->authorize('create-event');

        $input = $request->all();

        // Featured is a checkbox, so it gets sent as an array, but it needs to be saved as a boolean value
        if(isset($input['featured']) && is_array($input['featured']) && $input['featured'][0] == 'yes')
        {
            $input['featured'] = true;
        }
        else
        {
            $input['featured'] = false;
        }

        $event = Event::create($input);

        // Save event image if a file was uploaded
        if($request->hasFile('image'))
        {
            $event->image = $this->handleUpload($request);
        }

        $event->save();

        $request->session()->flash('success', 'Your event has been created.');
        return redirect('/event/' . $event->id);
    }

    // View an existing event
    public function view(Request $request, Event $event)
    {
        return view('pages/event/view', compact('event'));
    }

    //review volunteers for an existing event
    public function review(Request $request, Event $event)
    {
        return view('pages/event/user-shift-review', compact('event'));
    }

    // View form to edit an existing event
    public function editForm(Request $request, Event $event)
    {
        $this->authorize('edit-event');
        return view('pages/event/edit', compact('event'));
    }

    // Save edits to an existing event
    public function edit(EventRequest $request, Event $event)
    {
        $this->authorize('edit-event');
        $input = $request->all();

        // Featured is a checkbox, so it gets sent as an array, but it needs to be saved as a boolean value
        if(isset($input['featured']) && is_array($input['featured']) && $input['featured'][0] == 'yes')
        {
            $input['featured'] = true;
        }
        else
        {
            $input['featured'] = false;
        }

        $event->update($input);

        if($request->hasFile('image'))
        {
            $event->image = $this->handleUpload($request);
        }

        $event->save();
        event(new EventChanged($event, ['type' => 'event', 'status' => 'edited']));

        $request->session()->flash('success', 'Event has been updated.');
        return redirect('/event/' . $event->id);
    }

    // View confirmation page before deleting an event
    public function deleteForm(Request $request, Event $event)
    {
        $this->authorize('delete-event');
        return view('pages/event/delete', compact('event'));
    }

    // Delete an event
    public function delete(Request $request, Event $event)
    {

        $this->authorize('delete-event');

        event(new EventChanged($event, ['type' => 'event', 'status' => 'deleted']));

        $event->delete();

        $request->session()->flash('success', 'Event has been deleted.');
        return redirect('/');
    }

    // View confirmation page before cloning an event
    public function cloneForm(Request $request, Event $event)
    {
        $this->authorize('create-event');
        return view('pages/event/clone', compact('event'));
    }

    // Clone an event
    public function cloneEvent(Request $request, Event $event)
    {
        $this->authorize('create-event');
        $this->validate($request,
        [
            'start_date' => 'required|date_format:Y-m-d',
        ]);

        // Set up event information
        $startDate = new Carbon($event->start_date);
        $endDate = new Carbon($event->end_date);
        $newStartDate = new Carbon($request->input('start_date'));
        $departments = $event->departments;

        // Find the difference of the start dates
        $difference = $startDate->diffInSeconds($newStartDate);

        // Create new event from old event data
        $newEvent = Event::create([
            'name' => $event->name,
            'description' => $event->description,
            'start_date' => $startDate->addSeconds($difference)->format('Y-m-d'),
            'end_date' => $endDate->addSeconds($difference)->format('Y-m-d'),
        ]);

        // Add the image manually because it's not automatically fillable
        if($event->image)
        {
            $newEvent->image = $event->image;
            $newEvent->save();
        }

        // Loop through event departments
        foreach($departments as $department)
        {
            // Create new department
            $newDepartment = new Department;
            $newDepartment->event_id = $newEvent->id;
            $newDepartment->save();

            // Because the event_id isn't fillable, we have to define it first and then update
            $newDepartment->update([
                'name' => $department->name,
                'description' => $department->description
            ]);

            // Loop through shifts
            $shifts = $department->shifts;

            foreach($shifts as $shift)
            {
                $newShift = Shift::create([
                    'event_id' => $newEvent->id,
                    'department_id' => $newDepartment->id,
                    'name' => $shift->name,
                    'description' => $shift->description,
                ]);

                // Loop through roles for this shift
                $shiftRoles = $shift->roles;

                foreach($shiftRoles as $role)
                {
                    EventRole::create([
                        'role_id' => $role->role_id,
                        'event_id' => $newEvent->id,
                        'foreign_id' => $newShift->id,
                        'foreign_type' => $role->foreign_type
                    ]);
                }

                // Loop through the schedule for this shift
                $scheduled = $shift->schedule;

                foreach($scheduled as $schedule)
                {
                    $newStartDate = new Carbon($schedule->start_date);
                    $newEndDate = new Carbon($schedule->end_date);
                    $newDates = json_decode($schedule->dates);

                    foreach($newDates as $index => $date)
                    {
                        $newDate = new Carbon($date);
                        $newDates[$index] = $newDate->addSeconds($difference)->format('Y-m-d');
                    }

                    $newSchedule = Schedule::create([
                        'department_id' => $newDepartment->id,
                        'shift_id' => $newShift->id,
                        'start_date' => $newStartDate->addSeconds($difference)->format('Y-m-d'),
                        'end_date' => $newEndDate->addSeconds($difference)->format('Y-m-d'),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'duration' => $schedule->duration,
                        'volunteers' => $schedule->volunteers,
                        'dates' => json_encode($newDates),
                        'password' => $schedule->password,
                    ]);

                    // Loop through roles for this schedule
                    $scheduleRoles = $schedule->roles;

                    foreach($scheduleRoles as $role)
                    {
                        EventRole::create([
                            'role_id' => $role->role_id,
                            'event_id' => $newEvent->id,
                            'foreign_id' => $newSchedule->id,
                            'foreign_type' => $role->foreign_type
                        ]);
                    }

                    Slot::generate($newSchedule);
                }
            }
        }

        $request->session()->flash('success', 'Event has been cloned.');
        return redirect('/event/' . $newEvent->id);
    }
}
