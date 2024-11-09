<div class=" w-full bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6">
    <div class="flex justify-between">
        <div>
            <h5 class="leading-none text-3xl font-bold text-gray-900 dark:text-white pb-2">32.4k</h5>
            <p class="text-base font-normal text-gray-500 dark:text-gray-400">Upload File this week</p>
        </div>
        <div
            class="flex items-center px-2.5 py-0.5 text-base font-semibold text-green-500 dark:text-green-500 text-center">
            <h1 id="percentage">1%</h1>
            <svg class="w-3 h-3 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 10 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 13V1m0 0L1 5m4-4 4 4" />
            </svg>
        </div>
    </div>
    <div id="area-chart"></div>

</div>


<script>
    // Function to fetch area chart data
    async function fetchAreaChartData() {
        try {
            const response = await fetch('/api/getAreaChart'); // Adjust the URL if needed
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();

            // Log the percentageChange received
            console.log('Percentage Change:', data.percentageChange);

            // Extract categories and series data
            const categories = data.categories;
            const seriesData = data.series[0].data.map(point => point.y); // Extract y values

            // Display the total uploads in a human-readable format (e.g., "32.4k")
            const totalUploads = data.totalUploads;
            const totalUploadsFormatted = totalUploads >= 1000 ? (totalUploads / 1000).toFixed(1) + 'k' :
                totalUploads;
            document.querySelector('.text-3xl').innerText = totalUploadsFormatted;

            // Calculate the percentage change for uploads (e.g., "12%")
            const percentageChange = data.percentageChange;
            const percentageFormatted = percentageChange >= 0 ?
                `+${percentageChange}%` :
                `${percentageChange}%`;

            // Log the final percentage to be displayed
            console.log('Formatted Percentage Change:', percentageFormatted);

            // Display the percentage change (e.g., "+12%")
            document.getElementById('percentage').innerText = `Uploads this week: ${percentageFormatted}`;

            // Prepare the chart options
            const options = {
                chart: {
                    height: "100%",
                    maxWidth: "100%",
                    type: "area",
                    fontFamily: "Inter, sans-serif",
                    dropShadow: {
                        enabled: false,
                    },
                    toolbar: {
                        show: false,
                    },
                },
                tooltip: {
                    enabled: true,
                    x: {
                        show: true,
                    },
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.55,
                        opacityTo: 0,
                        shade: "#1C64F2",
                        gradientToColors: ["#1C64F2"],
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    width: 6,
                },
                grid: {
                    show: false,
                    strokeDashArray: 4,
                    padding: {
                        left: 2,
                        right: 2,
                        top: 0
                    },
                },
                series: [{
                    name: "Uploads",
                    data: seriesData,
                    color: "#1A56DB",
                }],
                xaxis: {
                    categories: categories,
                    labels: {
                        show: true,
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    show: true,
                },
            };

            // Render the chart
            if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById("area-chart"), options);
                chart.render();
            }
        } catch (error) {
            console.error('Error fetching area chart data:', error);
        }
    }

    // Call the function to fetch data and render the chart
    fetchAreaChartData();
</script>
