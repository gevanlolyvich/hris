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

class EventEmployeeController extends Controller
{
    public function report(Request $request)
    {
        $event_employee = EventEmployee::find($request->eventemployeeid);
        if ($event_employee) {
            // dd($event_employee);

            $document_path = null;
            if ($request->file('myDocument')) {
                Log::info('Document detected');
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $request->title) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs("uploads/events/{{ $request->eventemployeeid }}", $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
                Log::info($document_path);
            }

            $event_employee->report_note     = $request->note;
            $event_employee->report_document = $document_path;
            $event_employee->save();
            return redirect()->back()->with('success', __('Report Successfully Added'));
        } else {
            return redirect()->back()->with('error', __('Failed Adding Report.'));    
        }
    }
}
