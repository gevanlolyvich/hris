<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event as LocalEvent ;
use App\Models\EventEmployee;
use App\Models\Projects;
use App\Models\Tasks;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventEmployeeController extends Controller
{
    public function report(Request $request)
    {
        $event_employee = EventEmployee::find($request->eventemployeeid);
        if ($event_employee) {
            $document_path = null;
            $employee = Employee::where('is_active', 1)->where('user_id', Auth::user()->id)->first();

            if(empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            if ($event_employee->report_document && $request->file('myDocument')) {
                $filepath_array = explode('/', $event_employee->report_document);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/events/$event_employee->event_id/report/$filename")) {
                    Storage::disk('public')->delete("uploads/events/$event_employee->event_id/report/$filename");
                }
            }

            if ($request->file('myDocument')) {
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs("uploads/events/$event_employee->event_id/report", $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $event_employee->report_note     = $request->note;
            $event_employee->report_document = $document_path;
            $event_employee->save();
            return redirect()->back()->with('success', __('Report Successfully Added'));
        } else {
            return redirect()->back()->with('error', __('Failed Adding Report'));
        }
    }

    public function attendance(Request $request)
    {
        $event_employee = EventEmployee::find($request->eventemployeeidAttendance ?? $request->eventemployeeidAttendanceOut);

        $picture_path = null;
        $employee = Employee::where('is_active', 1)->where('user_id', Auth::user()->id)->first();

        if(empty($employee) || !$employee) {
            return redirect()->back()->with('error', __('Inactive'));
        }

        if ($request->out == '1' && $event_employee) {
            // clock out
            Log::info('Clock Out');

            // delete old picture file
            if ($event_employee->picture_out) {
                $filepath_array = explode('/', $event_employee->picture_out);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/events/$event_employee->event_id/attendance/clock_out/$filename")) {
                    Storage::disk('public')->delete("uploads/events/$event_employee->event_id/attendance/clock_out/$filename");
                }
            }

            // process image file
            if ($request->input('picture_out')) {
                $base64ImageData = $request->input('picture_out');
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
                $pictureName = 'attendance_'.time().'_'.date('Y-m-d').'_'.preg_replace('/\s+/', '', $employee->name).'.png';
                Storage::disk('public')->put("uploads/events/$event_employee->event_id/attendance/clock_out/$pictureName", $imageData);
                $picture_path = env('APP_URL') . "/storage/uploads/events/$event_employee->event_id/attendance/clock_out/$pictureName";
            }

            $event_employee->clock_out   = date('Y-m-d H:i:s');
            $event_employee->coord_out   = "$request->latitude, $request->longitude, $request->accuracy";
            $event_employee->picture_out = $picture_path;
            $event_employee->save();

            return redirect()->back()->with('success', __('Assigment Attendance Successfully Added'));
        } elseif ($event_employee) {
            // clock in
            Log::info('Clock In');

            // delete old picture file
            if ($event_employee->picture_in) {
                $filepath_array = explode('/', $event_employee->picture_in);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/events/$event_employee->event_id/attendance/clock_in/$filename")) {
                    Storage::disk('public')->delete("uploads/events/$event_employee->event_id/attendance/clock_in/$filename");
                }
            }

            // process image file
            if ($request->input('picture')) {
                $base64ImageData = $request->input('picture');
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
                $pictureName = 'attendance_'.time().'_'.date('Y-m-d').'_'.preg_replace('/\s+/', '', $employee->name).'.png';
                Storage::disk('public')->put("uploads/events/$event_employee->event_id/attendance/clock_in/$pictureName", $imageData);
                $picture_path = env('APP_URL') . "/storage/uploads/events/$event_employee->event_id/attendance/clock_in/$pictureName";
            }

            $event_employee->clock_in   = date('Y-m-d H:i:s');
            $event_employee->coord_in   = "$request->latitude, $request->longitude, $request->accuracy";
            $event_employee->picture_in = $picture_path;
            $event_employee->save();

            return redirect()->back()->with('success', __('Assigment Attendance Successfully Added'));
        } else {
            return redirect()->back()->with('error', __('Failed Adding Assigment Attendance'));
        }
    }
}
