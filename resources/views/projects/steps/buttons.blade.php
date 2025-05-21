<!-- Navigation Buttons -->
<div class="flex justify-between mt-8">
    <button type="button" id="prev-button" class="hidden px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
        Previous
    </button>
    <button type="button" id="next-button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        Next
    </button>
    <button type="submit" id="submit-button" class="hidden px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
        {{ isset($project) ? 'Update Project' : 'Create Project' }}
    </button>
</div>