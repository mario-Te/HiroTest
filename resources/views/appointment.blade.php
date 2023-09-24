<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Appoinements') }}
        </h2>
    </x-slot>

 <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
         <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
               @if (session('success'))
               <span class="inline-flex items-center w-full px-2.5 py-0.5 rounded-full text-lg font-medium bg-green-500 text-white justify-center">
               {{session('success')}}
               </span>
               @endif
               <form action="{{ route('events.store') }}" method="POST">
                  @csrf
                  <!-- Symmary -->
                  <div>
                     <x-input-label for="Summary" :value="__('Summary')" />
                     <x-text-input id="Summary" class="block mt-1 w-full" type="text" name="summary" value="{{ $event->start ?? '' }}" required autofocus  />
                     
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
                 
                  <div class="flex items-center justify-end mt-4">
                     <x-primary-button class="ml-4">
                        {{ __('Add Appointment') }}
                     </x-primary-button>
                  </div>
               </form>
            
            </div>
         </div>
      </div>
   </div>
</x-app-layout>
