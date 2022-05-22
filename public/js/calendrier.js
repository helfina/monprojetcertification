window.onload = () =>{
    let calendarEl = document.querySelector('#calendar');
    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: "fr",
        timeZone: "UTC",
        initialDate: '2022-05-12',
        headerToolbar: {
            left: "prevYear,prev,next,nextYear today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek"
        },
    });
    calendar.render();
}
