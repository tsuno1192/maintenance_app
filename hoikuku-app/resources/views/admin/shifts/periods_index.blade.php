<x-app-layout>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">シフト提出期間カレンダー</h1>
        
        <!-- カレンダーを表示するエリア（高さを確実に指定） -->
        <div id="calendar" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); min-height: 700px;"></div>
    </div>

    <!-- FullCalendarのCDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ja',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: "{{ route('admin.shifts.periods.json') }}"
            });
            calendar.render();
        });
    </script>
</x-app-layout>