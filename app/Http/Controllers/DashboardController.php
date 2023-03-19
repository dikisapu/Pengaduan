<?php

namespace App\Http\Controllers;

use App\Models\{Complaint, Officer, Response, Student};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isFalse;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Timezone
        $currentTime = Carbon::now();
        $hour = $currentTime->hour;

        if ($hour >= 5 && $hour <= 11) {
            $greeting = 'Selamat pagi';
        } elseif ($hour >= 12 && $hour <= 17) {
            $greeting = 'Selamat sore';
        } else {
            $greeting = 'Selamat malam';
        }

        // Complaints
        $complaints = Complaint::orderByDesc("created_at")->get() ?? [];

        // Officers
        $officers = Officer::all();

        // Students
        $students = Student::all();

        // Responses
        $responses = Response::orderByDesc("created_at")->get() ?? [];

        return view("dashboard.index", [
            "title" => "Dashboard",
            "greeting" => $greeting,
            "complaints" => $complaints,
            "officers" => $officers,
            "students" => $students,
            "responses" => $responses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function responsesData()
    {
        // Retrieve responses created in the last 7 days
        $responses = DB::table('responses')
            ->where("responses.officer_nik", auth()->user()->nik)
            ->whereBetween('responses.created_at', [Carbon::now()->subDays(6), Carbon::now()])
            // Select the date of creation and count of responses for each day
            ->select(
                DB::raw('DATE(responses.created_at) as date'),
                DB::raw('COUNT(*) as count'),
            )
            // Group the results by the date of creation
            ->groupBy('date')
            // Order the results by date in ascending order
            ->orderBy('date', 'asc')
            // Execute the query and retrieve the results
            ->get();

        // Create an array with the dates for the last 7 days
        $dateRange = Carbon::now()->subDays(6)->toPeriod(Carbon::now());

        // Create an array to store the response counts for each day
        $xAxis = [];
        $yAxis = [];

        // Loop through the date range and populate the counts array with the response counts
        foreach ($dateRange as $date) {
            $formattedDate = $date->format('Y-m-d');
            $count = $responses->where('date', $formattedDate)->pluck('count')->first();
            $yAxis[] = $count ?? 0; // Use 0 if the count is null
            $xAxis[] = $date->format("Y-m-d");
        }

        // Retrieve genders which based on responses
        $genders = DB::table('responses')
            ->join("officers", 'responses.officer_nik', "=", "officers.officer_nik")
            ->join("users", 'officers.officer_nik', "=", "users.nik")
            ->select(
                DB::raw('SUM(CASE WHEN users.gender = "L" THEN 1 ELSE 0 END) as male'),
                DB::raw('SUM(CASE WHEN users.gender = "P" THEN 1 ELSE 0 END) as female'),
            )->get()[0];

        $chartData = [
            // Use the response counts as chart data
            'data' => [
                // Records of responses
                "yAxis" => $yAxis,
                // Labels of responses
                "xAxis" => $xAxis,
                // Genders of responses
                "genders" => $genders
            ],
        ];

        // Return the chart data as a JSON response
        return response()->json($chartData);
    }
}
