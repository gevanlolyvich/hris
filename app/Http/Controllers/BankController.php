<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {

        if (\Auth::user()->can('Manage Bank')) {
            $banks = Bank::orderBy('name', 'ASC')->get();

            return view('bank.index', compact('banks'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Bank')) {

            return view('bank.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Create Bank')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:banks,name',
                    'code' => 'required|max:3|unique:banks,code',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $bank                = new Bank();
            $bank->name          = $request->name;
            $bank->code          = $request->code;
            $bank->save();

            return redirect()->route('bank.index')->with('success', __('Bank successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Bank $bank)
    {
        return redirect()->route('bank.index');
    }

    public function edit(Bank $bank)
    {
        if (\Auth::user()->can('Edit Bank')) {
            return view('bank.edit', compact('bank'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Bank $bank)
    {
        if (\Auth::user()->can('Edit Bank')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:banks,name,' . $bank->id,
                    'code' => 'required|max:3|unique:banks,code,' . $bank->id,
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $bank->name = $request->name;
            $bank->code = $request->code;
            $bank->save();

            return redirect()->route('bank.index')->with('success', __('Bank successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Bank $bank)
    {
        if (\Auth::user()->can('Delete Bank')) {
            $bank->delete();

            return redirect()->route('bank.index')->with('success', __('Bank successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
