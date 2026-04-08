function initializeCharts() {
  // Wait for Chart.js to be fully loaded
  if (typeof Chart === "undefined") {
    setTimeout(initializeCharts, 500);
    return;
  }

  // Bookings Overview Chart (Line Chart)
  const bookingsChartElement = document.getElementById("bookingsChart");
  if (bookingsChartElement) {
    const ctx = bookingsChartElement.getContext("2d");

    // Check if data exists and has content
    const monthlyData = window.monthlyBookingsData || [];

    // Destroy existing chart if it exists
    if (window.bookingsChartInstance) {
      window.bookingsChartInstance.destroy();
    }

    if (monthlyData && monthlyData.length > 0) {
      const labels = monthlyData.map((item) => {
        return item.month || item.label || "Unknown";
      });

      const data = monthlyData.map((item) => {
        return parseInt(item.count) || 0;
      });

      try {
        window.bookingsChartInstance = new Chart(ctx, {
          type: "line",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Monthly Bookings",
                data: data,
                backgroundColor: "rgba(78, 115, 223, 0.1)",
                borderColor: "rgba(78, 115, 223, 1)",
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 4,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: "top",
              },
              title: {
                display: true,
                text: "Booking Trends",
              },
            },
            scales: {
              x: {
                grid: {
                  display: false,
                },
                title: {
                  display: true,
                  text: "Month",
                },
              },
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1,
                },
                title: {
                  display: true,
                  text: "Number of Bookings",
                },
              },
            },
          },
        });
      } catch (error) {
        console.error("Error creating bookings chart:", error);
      }
    } else {
      // Display "No data" message
      ctx.clearRect(
        0,
        0,
        bookingsChartElement.width,
        bookingsChartElement.height,
      );
      ctx.fillStyle = "#6c757d";
      ctx.font = "16px Arial";
      ctx.textAlign = "center";
      ctx.fillText(
        "No booking data available",
        bookingsChartElement.width / 2,
        bookingsChartElement.height / 2,
      );
    }
  }

  // Status Distribution Chart - Clean version with percentages on slices
  const statusChartElement = document.getElementById("statusChart");
  if (statusChartElement) {
    // Destroy any existing chart instance properly
    const existingChart = Chart.getChart(statusChartElement);
    if (existingChart) {
      existingChart.destroy();
    }

    const ctx = statusChartElement.getContext("2d");
    const statusData = window.statusDistributionData || {};

    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData).map(
      (val) => parseInt(val) || 0,
    );
    const hasData = statusValues.some((value) => value > 0);

    if (hasData) {
      window.statusChartInstance = new Chart(ctx, {
        type: "pie",
        data: {
          labels: statusLabels.map(
            (label) => label.charAt(0).toUpperCase() + label.slice(1),
          ),
          datasets: [
            {
              data: statusValues,
              backgroundColor: [
                "#ffc107",
                "#28a745",
                "#17a2b8",
                "#0d6efd",
                "#6c757d",
                "#f39c12",
                "#dc3545",
              ],
              borderWidth: 2,
              borderColor: "#ffffff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "right",
              labels: {
                boxWidth: 15,
                padding: 15,
                font: { size: 13 },
                generateLabels: function (chart) {
                  const data = chart.data;
                  if (data.labels.length && data.datasets.length) {
                    const total = data.datasets[0].data.reduce(
                      (a, b) => a + b,
                      0,
                    );
                    return data.labels.map((label, i) => {
                      const value = data.datasets[0].data[i];
                      const percentage = ((value / total) * 100).toFixed(0);
                      return {
                        text: `${label} (${percentage}%)`,
                        fillStyle: data.datasets[0].backgroundColor[i],
                        hidden: false,
                        index: i,
                      };
                    });
                  }
                  return [];
                },
              },
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const value = context.parsed;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((value / total) * 100).toFixed(1);
                  return `${context.label}: ${value} (${percentage}%)`;
                },
              },
            },
            datalabels: {
              color: "#fff",
              font: { weight: "bold", size: 14 },
              formatter: function (value, context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(0);
                return percentage + "%";
              },
            },
          },
        },
        plugins: [ChartDataLabels],
      });
    }
  }

  // Chart initialization completed
}

// Dashboard Data Management
