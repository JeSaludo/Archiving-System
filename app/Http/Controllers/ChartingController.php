<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\TreeCuttingPermit;
use App\Models\TreeCuttingPermitDetail;
use App\Models\ChainsawRegistration;
class ChartingController extends Controller
{
    //

    public function permitStatistics(Request $request)
    {
        $timeRange = $request->query('time_range', 'monthly');

        // Adjust query format for Monthly or Yearly
        $dateFormat = $timeRange === 'yearly' ? '%Y' : '%Y-%m';

        $query = DB::table('files')
            ->selectRaw("
                DATE_FORMAT(date_released, ?) as period, 
                municipality, 
                COUNT(id) as total
            ", [$dateFormat]) // Pass the binding directly here
            ->groupBy('period', 'municipality')
            ->orderBy('period');

        $data = $query->get();

        return response()->json($data);
    }
    public function GetTreeCuttingStatistics(Request $request)
    {
        $municipality = $request->query('municipality'); // Example: "Gasan"
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly' if not provided

        // Base Query: Filter by permit type
        $query = File::where('permit_type', 'tree-cutting-permits')
            ->whereNotNull('date_released');

        // Apply municipality filter if provided
        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year')
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc');
        } else { // Default to monthly grouping
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year'),
                DB::raw("DATE_FORMAT(date_released, '%b') as month") // Format month as "Jan", "Feb"
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'), DB::raw("DATE_FORMAT(date_released, '%b')"))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc'); // Correct order
        }

        // Get results
        $data = $query->get();
        $totalCount = $data->sum('count'); // Calculate total count

        // Return JSON response
        return response()->json([
            'data' => $data,
            'total_count' => $totalCount,
        ]);
    }
    public function GetTreeCuttingSpeciesChartData(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly');
        $municipality = $request->query('municipality'); // Optional filter
        $species = $request->query('species'); // Optional filter

        // Base Query: Normalize species names
        $query = DB::table('tree_cutting_permit_details as details')
            ->join('tree_cutting_permits as permits', 'details.tree_cutting_permit_id', '=', 'permits.id')
            ->join('files', 'permits.file_id', '=', 'files.id')
            ->whereNotNull('details.date_applied') // Correct date field
            ->select(
                DB::raw('SUM(details.number_of_trees) as total_trees'),
                DB::raw("LOWER(TRIM(details.species)) as species"),
                'files.municipality',
                DB::raw('YEAR(details.date_applied) as year'), // Correct date field
                DB::raw("DATE_FORMAT(details.date_applied, '%b %Y') as month"), // Correct date field
                'details.date_applied' // Ensure this is in GROUP BY
            );

        // Apply filters
        if ($municipality) {
            $query->where('files.municipality', $municipality);
        }
        if ($species) {
            $query->whereRaw("LOWER(TRIM(details.species)) = ?", [strtolower(trim($species))]);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy(
                DB::raw("LOWER(TRIM(details.species))"),
                'files.municipality',
                DB::raw('YEAR(details.date_applied)'),
                DB::raw("DATE_FORMAT(details.date_applied, '%b %Y')"), // ✅ Add this
                'details.date_applied' // ✅ Add this
            );
        } else { // Default to monthly
            $query->groupBy(
                DB::raw("LOWER(TRIM(details.species))"),
                'files.municipality',
                DB::raw('YEAR(details.date_applied)'),
                DB::raw("DATE_FORMAT(details.date_applied, '%b')"),
                'details.date_applied' // 🔥 Added this line
            )
                ->orderBy(DB::raw('YEAR(details.date_applied)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(details.date_applied, '%b'), '%b')"), 'asc');
        }

        // Fetch results
        $data = $query->get();

        return response()->json($data);
    }


    public function GetTreeCuttingByCategory(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly'); // 'monthly' or 'yearly'
        $municipality = $request->query('municipality'); // Optional filter

        // Base Query
        $query = DB::table('tree_cutting_permit_details as details')
            ->join('tree_cutting_permits as permits', 'details.tree_cutting_permit_id', '=', 'permits.id')
            ->join('files', 'permits.file_id', '=', 'files.id') // Join to get municipality
            ->whereNotNull('details.date_applied')
            ->select(
                'permits.permit_type',
                DB::raw('SUM(details.number_of_trees) as total_trees'),
                DB::raw('YEAR(details.date_applied) as year'),
                DB::raw("DATE_FORMAT(details.date_applied, '%b') as month") // Ensure it's included in GROUP BY
            );

        // Apply Municipality Filter
        if ($municipality) {
            $query->where('files.municipality', $municipality);
        }

        // Adjust Grouping Based on Timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy(
                'permits.permit_type',
                DB::raw('YEAR(details.date_applied)'),
                DB::raw("DATE_FORMAT(details.date_applied, '%b')")
            );

        } else { // Default to Monthly
            $query->groupBy(
                'permits.permit_type',
                DB::raw('YEAR(details.date_applied)'),
                DB::raw("DATE_FORMAT(details.date_applied, '%b')")
            ) // Added missing group by field
                ->orderBy(DB::raw('YEAR(details.date_applied)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(details.date_applied, '%b'), '%b')"), 'asc');
        }

        // Fetch Data
        $data = $query->get();

        return response()->json($data);
    }

    //Get Chainsaw Registration By Permit Count
    public function getChainsawRegistrationStatistics(Request $request)
    {
        $municipality = $request->query('municipality', 'All'); // Default to 'All'
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly'

        $query = DB::table('files')
            ->where('permit_type', 'chainsaw-registration')
            ->whereNotNull('date_released')
            ->selectRaw("
                municipality,
                COUNT(id) as total_permits,
                YEAR(date_released) as year" .
                ($timeframe === 'monthly' ? ", MONTH(date_released) as month_number, DATE_FORMAT(date_released, '%b') as month" : "")
            );

        if ($municipality !== 'All') {
            $query->where('municipality', $municipality);
        }

        if ($timeframe === 'monthly') {
            $query->groupBy('municipality', DB::raw('YEAR(date_released), MONTH(date_released), DATE_FORMAT(date_released, "%b")'))
                ->orderByRaw('YEAR(date_released) ASC, MONTH(date_released) ASC');
        } else {
            $query->groupBy('municipality', DB::raw('YEAR(date_released)'))
                ->orderByRaw('YEAR(date_released) ASC');
        }

        $data = $query->get();
        $totalCount = $data->sum('total_permits'); // Calculate total permits

        return response()->json([
            'data' => $data,
            'total_count' => $totalCount,
        ]);
    }

    public function getChainsawRegistrationStatisticsByCategory(Request $request)
    {
        $municipality = $request->query('municipality', 'All'); // Default to 'All'
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly'

        $query = DB::table('files')
            ->where('permit_type', 'chainsaw-registration')
            ->whereNotNull('date_released')
            ->selectRaw("
                municipality,
                COUNT(CASE WHEN category = 'new' THEN 1 END) AS new_registrations,
                COUNT(CASE WHEN category = 'renewal' THEN 1 END) AS renewals,
                YEAR(date_released) as year" .
                ($timeframe === 'monthly' ? ", DATE_FORMAT(date_released, '%b') as month" : "") // Format month as "Jan", "Feb"
            );

        if ($municipality !== 'All') {
            $query->where('municipality', $municipality);
        }

        if ($timeframe === 'monthly') {
            $query->groupBy('municipality', DB::raw('YEAR(date_released), DATE_FORMAT(date_released, "%b")'))
                ->orderByRaw('YEAR(date_released) ASC, STR_TO_DATE(DATE_FORMAT(date_released, "%b"), "%b") ASC');
        } else {
            $query->groupBy('municipality', DB::raw('YEAR(date_released)'))
                ->orderByRaw('YEAR(date_released) ASC');
        }

        $query->havingRaw("new_registrations > 0 OR renewals > 0");

        $data = $query->get();
        $totalCount = $data->sum(fn($item) => $item->new_registrations + $item->renewals); // Calculate total registrations

        return response()->json([
            'data' => $data,
            'total_count' => $totalCount,
        ]);
    }

    public function GetPrivateTreePlantationRegistrations(Request $request)
    {
        $municipality = $request->query('municipality'); // Example: "Gasan"
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly' if not provided

        // Base Query: Filter by permit type
        $query = File::where('permit_type', 'tree-plantation-registration')
            ->whereNotNull('date_released');

        // Apply municipality filter if provided
        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year')
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc');
        } else { // Default to monthly grouping
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year'),
                DB::raw("DATE_FORMAT(date_released, '%b') as month") // Format month as "Jan", "Feb"
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'), DB::raw("DATE_FORMAT(date_released, '%b')"))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc'); // Correct order
        }

        // Get results
        $data = $query->get();
        $totalCount = $data->sum('count'); // Calculate total count

        // Return JSON response
        return response()->json([
            'data' => $data,
            'total_count' => $totalCount,
        ]);
    }


    //Tree Transport

    public function GetTreeTransportStatistics(Request $request)
    {
        $municipality = $request->query('municipality'); // Example: "Boac"
        $timeframe = $request->query('timeframe', 'monthly'); // Default: 'monthly'

        // Base Query: Filter by permit type
        $query = File::where('permit_type', 'transport-permit')
            ->whereNotNull('date_released');

        // Apply municipality filter if provided
        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year')
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc');
        } else { // Default: Monthly grouping
            $query->select(
                DB::raw('count(*) as count'),
                'municipality',
                DB::raw('YEAR(date_released) as year'),
                DB::raw("DATE_FORMAT(date_released, '%b') as month") // Month formatted as "Jan", "Feb", etc.
            )
                ->groupBy('municipality', DB::raw('YEAR(date_released)'), DB::raw("DATE_FORMAT(date_released, '%b')"))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc'); // Ensures correct month order
        }

        // Get results
        $data = $query->get();
        $totalCount = $data->sum('count'); // Total count of transport permits issued

        // Return JSON response
        return response()->json([
            'data' => $data,
            'total_count' => $totalCount,
        ]);
    }


    public function GetTreeTransportSpeciesChartData(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly'
        $municipality = $request->query('municipality'); // Optional municipality filter
        $species = $request->query('species'); // Optional species filter

        // Base Query: Normalize species names
        $query = DB::table('tree_transport_permit_details as details')
            ->join('transport_permits as permits', 'details.transport_permit_id', '=', 'permits.id')
            ->join('files', 'permits.file_id', '=', 'files.id')
            ->whereNotNull('details.date_of_transport') // Use correct date field
            ->select(
                DB::raw('SUM(details.number_of_trees) as total_trees'),
                DB::raw("LOWER(TRIM(details.species)) as species"),
                'files.municipality',
                DB::raw('YEAR(details.date_of_transport) as year'),
                DB::raw("DATE_FORMAT(details.date_of_transport, '%b %Y') as month"),
                'details.date_of_transport'
            );

        // Apply filters
        if ($municipality) {
            $query->where('files.municipality', $municipality);
        }
        if ($species) {
            $query->whereRaw("LOWER(TRIM(details.species)) = ?", [strtolower(trim($species))]);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy(
                DB::raw("LOWER(TRIM(details.species))"),
                'files.municipality',
                DB::raw('YEAR(details.date_of_transport)'),
                DB::raw("DATE_FORMAT(details.date_of_transport, '%b %Y')"),
                'details.date_of_transport'
            );
        } else { // Default to monthly
            $query->groupBy(
                DB::raw("LOWER(TRIM(details.species))"),
                'files.municipality',
                DB::raw('YEAR(details.date_of_transport)'),
                DB::raw("DATE_FORMAT(details.date_of_transport, '%b')"),
                'details.date_of_transport'
            )
                ->orderBy(DB::raw('YEAR(details.date_of_transport)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(details.date_of_transport, '%b'), '%b')"), 'asc');
        }

        // Fetch results
        $data = $query->get();

        return response()->json($data);
    }


    public function GetLandTitleChartData(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly'); // Default timeframe
        $municipality = $request->query('municipality'); // Optional filter
        $category = $request->query('category'); // Optional filter

        // Base Query
        $query = DB::table('files')
            ->whereNotNull('date_released') // Ensure only issued titles are counted
            ->where('permit_type', 'land-title') // Filter only land-title permit type
            ->select(
                DB::raw('COUNT(id) as total_land_titles'),
                'municipality',
                'category', // Grouping by category
                DB::raw('YEAR(date_released) as year')
            );

        // Apply filters
        if ($municipality) {
            $query->where('municipality', $municipality);
        }
        if ($category) {
            $query->where('category', $category);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy(
                'municipality',
                'category',
                DB::raw('YEAR(date_released)')
            );
        } else { // Default to monthly
            $query->addSelect(DB::raw("DATE_FORMAT(date_released, '%b %Y') as month"))
                ->groupBy(
                    'municipality',
                    'category',
                    DB::raw('YEAR(date_released)'),
                    DB::raw("DATE_FORMAT(date_released, '%b %Y')") // ✅ Added to GROUP BY
                )
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc');
        }

        // Fetch results
        $data = $query->get();

        return response()->json($data);
    }

    public function GetLandTitleStatistics(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly');
        $municipality = $request->query('municipality');
        $category = $request->query('category');

        $query = DB::table('files')
            ->where('permit_type', 'land-title')
            ->whereNotNull('date_released')
            ->select(
                DB::raw('COUNT(id) as total_land_titles'),
                'municipality',
                'category',
                DB::raw('YEAR(date_released) as year')
            );

        if ($municipality) {
            $query->where('municipality', $municipality);
        }
        if ($category) {
            $query->where('category', $category);
        }

        if ($timeframe === 'yearly') {
            $query->groupBy('municipality', 'category', DB::raw('YEAR(date_released)'));
        } else {
            $query->addSelect(DB::raw("DATE_FORMAT(date_released, '%b') as month"))
                ->groupBy('municipality', 'category', DB::raw('YEAR(date_released)'), DB::raw("DATE_FORMAT(date_released, '%b')"))
                ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
                ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc');
        }

        $data = $query->get();

        return response()->json($data);
    }

    // public function GetLocalTransportPermitChartData(Request $request)
    // {
    //     $timeframe = $request->query('timeframe', 'monthly'); // Default to monthly
    //     $municipality = $request->query('municipality'); // Optional filter

    //     // Base Query
    //     $query = DB::table('files')
    //         ->whereNotNull('date_released') // Only issued permits
    //         ->where('permit_type', 'local-transport-permit') // Filter by permit type
    //         ->select(
    //             DB::raw('COUNT(id) as total_permits'),
    //             'municipality',
    //             DB::raw('YEAR(date_released) as year')
    //         );

    //     // Apply optional municipality filter
    //     if ($municipality) {
    //         $query->where('municipality', $municipality);
    //     }

    //     // Adjust grouping based on timeframe
    //     if ($timeframe === 'yearly') {
    //         $query->groupBy('municipality', DB::raw('YEAR(date_released)'));
    //     } else { // Default to monthly
    //         $query->addSelect(DB::raw("DATE_FORMAT(date_released, '%b %Y') as month"))
    //             ->groupBy(
    //                 'municipality',
    //                 DB::raw('YEAR(date_released)'),
    //                 DB::raw("DATE_FORMAT(date_released, '%b %Y')")
    //             )
    //             ->orderBy(DB::raw('YEAR(date_released)'), 'asc')
    //             ->orderBy(DB::raw("STR_TO_DATE(DATE_FORMAT(date_released, '%b'), '%b')"), 'asc');
    //     }

    //     // Fetch results
    //     $data = $query->get();

    //     return response()->json($data);
    // }


    public function getTransportPermitChartData(Request $request)
    {
        $query = DB::table('local_transport_permits')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released) as year'),
                DB::raw('MONTH(local_transport_permits.date_released) as month'),
                DB::raw('COUNT(local_transport_permits.id) as total_permits')
            )
            ->groupBy('files.municipality', 'year', 'month')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC');

        // Optional: Filter by municipality if requested
        if ($request->has('municipality')) {
            $query->where('files.municipality', $request->query('municipality'));
        }

        return response()->json($query->get());
    }

    public function getTransportPermitsByMunicipality(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly');
        $municipality = $request->query('municipality', 'All');

        $query = DB::table('local_transport_permits')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                'files.municipality',
                DB::raw('COUNT(local_transport_permits.id) as total_permits'),
                DB::raw('YEAR(local_transport_permits.date_released) as year')
            )
            ->whereNotNull('local_transport_permits.date_released');

        // Add month to SELECT and GROUP BY only if timeframe is monthly
        if ($timeframe === 'monthly') {
            $query->addSelect(DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b') as month"))
                ->groupBy(
                    'files.municipality',
                    DB::raw('YEAR(local_transport_permits.date_released)'),
                    DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b')")
                );
        } else {
            $query->groupBy(
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released)')
            );
        }

        // Apply municipality filter if not "All"
        if ($municipality !== 'All') {
            $query->where('files.municipality', $municipality);
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function getBusinessOwnersByMunicipality(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly');
        $municipality = $request->query('municipality', 'All');

        $query = DB::table('local_transport_permits')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                'files.municipality',
                DB::raw('COUNT(DISTINCT local_transport_permits.business_farm_name) as total_business_owners'),
                DB::raw('YEAR(local_transport_permits.date_released) as year')
            )
            ->whereNotNull('local_transport_permits.date_released');

        // Add month to SELECT and GROUP BY only if timeframe is monthly
        if ($timeframe === 'monthly') {
            $query->addSelect(DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b') as month"))
                ->groupBy(
                    'files.municipality',
                    DB::raw('YEAR(local_transport_permits.date_released)'),
                    DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b')")
                );
        } else {
            $query->groupBy(
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released)')
            );
        }

        // Apply municipality filter if not "All"
        if ($municipality !== 'All') {
            $query->where('files.municipality', $municipality);
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function getSpeciesTransportedByMunicipality(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly'
        $municipality = $request->query('municipality', 'All'); // Default to 'All'
        $startDate = $request->query('start_date'); // Optional start date filter
        $endDate = $request->query('end_date'); // Optional end date filter
        $species = $request->query('species', 'All'); // Default to 'All'

        // Base Query: Fetch species data related to Local Transport Permits
        $query = DB::table('butterfly_details')
            ->join('local_transport_permits', 'butterfly_details.file_id', '=', 'local_transport_permits.file_id')
            ->join('butterfly_species', 'butterfly_details.butterfly_id', '=', 'butterfly_species.id')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                DB::raw("LOWER(TRIM(butterfly_species.common_name)) as species"), // Normalize species names
                DB::raw('SUM(butterfly_details.quantity) as total_species'),
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released) as year'),
                DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b') as month")
            )
            ->whereNotNull('local_transport_permits.date_released');

        // Apply municipality filter if not "All"
        if ($municipality !== 'All') {
            $query->where('files.municipality', $municipality);
        }

        // Apply species filter if not "All"
        if ($species !== 'All') {
            $query->whereRaw("LOWER(TRIM(butterfly_species.common_name)) = ?", [strtolower(trim($species))]);
        }

        // Apply date range filter if provided
        if ($startDate) {
            $query->where('local_transport_permits.date_released', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('local_transport_permits.date_released', '<=', $endDate);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy(
                DB::raw("LOWER(TRIM(butterfly_species.common_name))"),
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released)')
            );
        } else {
            $query->groupBy(
                DB::raw("LOWER(TRIM(butterfly_species.common_name))"),
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released)'),
                DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b')")
            );
        }

        // Fetch results
        $data = $query->get();

        return response()->json($data);
    }

    public function GetLocalTransportPermitChartData(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly'); // Default to 'monthly'
        $municipality = $request->query('municipality'); // Optional filter

        // Base Query
        $query = DB::table('local_transport_permits')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                'files.municipality',
                DB::raw('COUNT(local_transport_permits.id) as total_permits'),
                DB::raw('YEAR(local_transport_permits.date_released) as year'),
                DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b') as month")
            )
            ->whereNotNull('local_transport_permits.date_released');

        // Apply optional municipality filter
        if ($municipality) {
            $query->where('files.municipality', $municipality);
        }

        // Adjust grouping based on timeframe
        if ($timeframe === 'yearly') {
            $query->groupBy('files.municipality', DB::raw('YEAR(local_transport_permits.date_released)'));
        } else {
            $query->groupBy(
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released)'),
                DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b')")
            );
        }

        // Fetch results
        $data = $query->get();

        return response()->json($data);
    }

    public function downloadSpeciesTransportedReport(Request $request)
    {
        $timeframe = $request->query('timeframe', 'monthly');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Fetch data based on filters
        $query = DB::table('butterfly_details')
            ->join('local_transport_permits', 'butterfly_details.file_id', '=', 'local_transport_permits.file_id')
            ->join('butterfly_species', 'butterfly_details.butterfly_id', '=', 'butterfly_species.id')
            ->join('files', 'local_transport_permits.file_id', '=', 'files.id')
            ->select(
                DB::raw("LOWER(TRIM(butterfly_species.common_name)) as species"),
                DB::raw('SUM(butterfly_details.quantity) as total_species'),
                'files.municipality',
                DB::raw('YEAR(local_transport_permits.date_released) as year'),
                DB::raw("DATE_FORMAT(local_transport_permits.date_released, '%b') as month")
            )
            ->whereNotNull('local_transport_permits.date_released');

        if ($startDate) {
            $query->where('local_transport_permits.date_released', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('local_transport_permits.date_released', '<=', $endDate);
        }

        $data = $query->get();

        // Generate CSV content
        $csvContent = "Species,Total Species,Municipality,Year,Month\n";
        foreach ($data as $row) {
            $csvContent .= "{$row->species},{$row->total_species},{$row->municipality},{$row->year},{$row->month}\n";
        }

        // Return CSV as a response
        $filename = "species_report_{$startDate}_to_{$endDate}.csv";
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

}
