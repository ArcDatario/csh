const revenueCanvas = document.getElementById('revenueChart');
if (revenueCanvas) {
    const revenueCtx = revenueCanvas.getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar', // Changed from 'line' to 'bar'
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Revenue',
                data: [6500, 5900, 8000, 8100, 5600, 7500, 9000],
                backgroundColor: '#6366f1', // Solid color for bars
                borderColor: '#6366f1', // Optional: border color for bars
                borderWidth: 1 // Adjusted for bar graph
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return `Revenue: ₱${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, // Adjusted to start at zero for bar graph
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    },
                    ticks: {
                        color: '#0f172a',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#0f172a'
                    }
                }
            }
        }
    });
}
         
         // Traffic Chart (Doughnut Chart)
         const trafficCanvas = document.getElementById('trafficChart');
         if (trafficCanvas) {
             const trafficCtx = trafficCanvas.getContext('2d');
             const trafficChart = new Chart(trafficCtx, {
                 type: 'doughnut',
                 data: {
                     labels: ['DTF', 'Screen', 'Sublamination', 'Jersey'],
                     datasets: [{
                         data: [35, 25, 20, 20],
                         backgroundColor: [
                             '#6366f1',
                             '#10b981',
                             '#f59e0b',
                             '#f43f5e'
                         ],
                         borderWidth: 0
                     }]
                 },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     plugins: {
                         legend: {
                             position: 'right',
                             labels: {
                                 color: '#0f172a',
                                 boxWidth: 12,
                                 padding: 16,
                                 usePointStyle: true,
                                 pointStyle: 'circle'
                             }
                         },
                         tooltip: {
                             callbacks: {
                                 label: function(context) {
                                     return `${context.label}: ${context.raw}%`;
                                 }
                             }
                         }
                     },
                     cutout: '70%'
                 }
             });
         }
         
         // Make charts responsive on window resize
         window.addEventListener('resize', function() {
             if (revenueChart) {
                 revenueChart.resize();
             }
             if (trafficChart) {
                 trafficChart.resize();
             }
         });
         
         // Chart period buttons
         const chartBtns = document.querySelectorAll('.chart-btn');
         chartBtns.forEach(btn => {
             btn.addEventListener('click', () => {
                 // Remove active class from all buttons in the same group
                 btn.parentElement.querySelectorAll('.chart-btn').forEach(b => {
                     b.classList.remove('active');
                 });
                 
                 // Add active class to clicked button
                 btn.classList.add('active');
                 
                 // In a real app, you would update the chart data here
                 // For demo purposes, we'll just show a toast
               
             });
         });