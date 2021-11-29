<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvImportRequest;
use App\Imports\ContactsImport;
use App\Models\CsvData;
use App\Models\Contact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    public function parseImport(CsvImportRequest $request)
    {
        if ($request->has('header')) {
            $headings = (new HeadingRowImport)->toArray($request->file('csv_file'));
            $data = Excel::toArray(new ContactsImport, $request->file('csv_file'))[0];
        } else {
            $data = array_map('str_getcsv', file($request->file('csv_file')->getRealPath()));
        }
        if (count($data) > 0) {
            $csv_data = array_slice($data, 0, 2);
            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }

        return view('import_fields', [
            'headings' => $headings ?? null,
            'csv_data' => $data,
            'csv_data_file' => $csv_data_file
        ]);
    }

    public function processImport(Request $request)
    {
        $data = CsvData::find($request->csv_data_file_id);
        $csv_data = array_values(array_unique(json_decode($data->csv_data, true), SORT_REGULAR));
        $contactsAry = [];
        foreach ($csv_data as $row) {
            $now = now()->toDateTimeString();
            $fieldsAry = [];
            foreach (config('app.db_fields') as $index => $field) {
                if ($data->csv_header) {
                    $fieldsAry[$field] = $row[$request->fields[$field]];
                } else {
                    $fieldsAry[$field] = $row[$request->fields[$index]];
                }
            }
            $fieldsAry['created_at'] = $now;
            $fieldsAry['updated_at'] = $now;
            $contactsAry[] = $fieldsAry;
        }
        Contact::insert($contactsAry);
        return redirect()->route('contacts.index')->with('success', __('Import finished.'));
    }
}