<!-- Documents Section -->
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Documents</h2>

    <!-- Resume Upload -->
    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Resume <span class="text-red-500">*</span>
            <span class="text-gray-500">(PDF, DOC, DOCX - max 5MB)</span>
        </label>
        <div class="mt-1 flex items-center">
            <input type="file" name="resume" id="resume" required
                   accept=".pdf,.doc,.docx"
                   class="file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <label class="file-label text-sm text-gray-500 ml-2"></label>
            <span class="file-size text-sm text-gray-400 ml-2"></span>
        </div>
        @error('resume')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Cover Letter Upload -->
    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Cover Letter
            <span class="text-gray-500">(PDF, DOC, DOCX - max 5MB)</span>
        </label>
        <div class="mt-1 flex items-center">
            <input type="file" name="cover_letter" id="cover_letter"
                   accept=".pdf,.doc,.docx"
                   class="file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <label class="file-label text-sm text-gray-500 ml-2"></label>
            <span class="file-size text-sm text-gray-400 ml-2"></span>
        </div>
        @error('cover_letter')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
