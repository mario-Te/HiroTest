<x-app-layout>
   <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
         {{ __('Calendar') }}
      </h2>
   </x-slot>
   <div class="CalendarContainer">
      <div class=" p-12">
         <div id="calendar" class="w-1/2 ml-1/4"></div>
      </div>
   </div>
</x-app-layout>
<div class="modal fade" id="PopUpModal" tabindex="-1" role="dialog" aria-labelledby="PopUpModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="PopUpModalLabel">Event Status</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="py-12">
               <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                   <div class="p-6 text-gray-900 dark:text-gray-100">
                        @if (session('success'))
                        <span class="inline-flex items-center w-full px-2.5 py-0.5 rounded-full text-lg font-medium bg-green-500 text-white justify-center">
                        {{session('success')}}
                        </span>
                        @endif
                        <form >
                           @csrf
                           <!-- Summary -->
                           <div>
                              <x-input-label for="Summary" :value="__('Summary')" />
                              <x-text-input value="{{ $event->tile ?? '' }}" id="Summary" class="block mt-1 w-full" type="text" name="summary"  required autofocus  />
                              
                           </div>
                           <!-- Start Date -->
                           <div class="mt-4">
                              <x-input-label for="start" :value="__('Start Date and Time')" />
                              <x-text-input id="start" class="block mt-1 w-full" type="datetime-local" name="start"  required autofocus />
                           </div>
                           <!-- End Date -->
                           <div class="mt-4">
                              <x-input-label for="end" :value="__('End Date and Time')" />
                              <x-text-input id="end" class="block mt-1 w-full"
                                 type="datetime-local"
                                 name="end"
                                 required />
                           </div>
                           <div class="flex items-center justify-center mt-4">
                              <x-primary-button type="button" class="ml-4" id="UpdateEventButton">
                                 {{ __('Update') }}
                              </x-primary-button>
                              <x-danger-button type="button" id="deleteEventButton">
                                {{ __('Delete') }}
                              </x-danger-button>
                           </div>
                        </form>
                     </div>
               </div>
            </div>
         </div>
       
      </div>
   </div>
</div>
<script>
   $(document).ready(function() {
    // Initialize the calendar
    $('#calendar').fullCalendar({
        // Configure calendar options
        // ...
        
        // Fetch event data from backend API
        events: '/api/calendar/events',
        
        // Customize the rendering of each event
        eventRender: function(event, element) {
            element.find('.fc-title').text(event.summary);
            element.css('background-color', generateRandomColor);
        },
        
        // Handle event click or other interactions
        eventClick: function(event, jsEvent, view) {
            // Call the backend API to check if the user is authorized to delete the event
            checkAuthorizationAndShowModal(event.id);
              $('#Summary').val(event.summary);
               $('#start').val(moment(event.start._i).format('YYYY-MM-DDTHH:mm'));
              $('#end').val(moment(event.end._i).format('YYYY-MM-DDTHH:mm'));
        },
    });
   
    // Handle delete button click inside the confirmation modal
    $('#deleteEventButton').on('click', function() {
        // Get the event ID from the modal data attribute
        const eventId = $('#PopUpModal').data('eventId');
        
        // Delete the event from the backend
        deleteEvent(eventId);
        
        // Remove the event from the calendar
        $('#calendar').fullCalendar('removeEvents', eventId);
        
        // Close the modal
        $('#PopUpModal').modal('hide');
    });
   });
       $('#UpdateEventButton').on('click', function() {
        // Get the event ID from the form action URL
       
        const eventId = $('#PopUpModal').data('eventId');
        // Update the event using AJAX
        updateEvent(eventId);
         // Close the modal
        $('#PopUpModal').modal('hide');
    });
   
   // Function to check authorization on the backend and show the confirmation modal
   function checkAuthorizationAndShowModal(eventId) {
    // Call the backend API to check if the user is authorized to delete the event
    $.ajax({
        url: `/appointments/${eventId}/authorization`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        success: function(data) {
            if (data.authorized) {
                // Set the event ID in the modal data attribute
                $('#PopUpModal').data('eventId', eventId);
                
                // Show the confirmation modal
                $('#PopUpModal').modal('show');
            }
        },
        error: function(error) {
            // Handle any errors that occurred during the request
            console.error(error);
        }
    });
   }
      function generateRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
   
    function deleteEvent(eventId) {
    $.ajax({
        url: `/appointments/${eventId}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        success: function(data) {
            // Handle the response or perform any necessary actions
            console.log(data);
            // ...
        },
        error: function(error) {
            // Handle any errors that occurred during the request
            console.error(error);
        }
    });
   }
 
   function updateEvent(eventId) {
    // Get the form data
    const formData = {
        summary: $('#Summary').val(),
        start: $('#start').val(),
        end: $('#end').val()
    };
    
    // Send the update request using AJAX
    $.ajax({
        url: `/appointments/${eventId}`,
        type: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        success: function(data) {
            // Handle the response or perform any necessary actions
            console.log(data);
            
            // ...
             $('#calendar').fullCalendar('removeEvents');
              $('#calendar').fullCalendar('refetchEvents');
        },
        error: function(error) {
            // Handle any errors that occurred during the request
            console.error(error);
        }
    });
}

</script>