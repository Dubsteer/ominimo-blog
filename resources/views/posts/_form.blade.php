@csrf

@if ($method !== 'POST')
    @method($method)
@endif

<div class="space-y-6">
    <x-form-field
        name="title"
        label="Title"
        :value="$post?->title"
        :required="true"
        maxlength="160"
        autofocus
    />

    <div>
        <label for="content" class="block text-sm font-semibold text-slate-800">Experience or question</label>
        <textarea
            id="content"
            name="content"
            rows="12"
            maxlength="50000"
            required
            @if ($errors->has('content')) aria-invalid="true" aria-describedby="content-error" @endif
            class="mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition {{ $errors->has('content') ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-100' }}"
        >{{ old('content', $post?->content) }}</textarea>
        @error('content')
            <p id="content-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-slate-600">Minimum 30 characters. Do not include names, contact details, claim or policy numbers, payment data, or medical information.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="claim_stage" class="block text-sm font-semibold text-slate-800">Claim stage</label>
            <select id="claim_stage" name="claim_stage" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
                <option value="">Select a stage</option>
                @foreach ($claimStages as $claimStage)
                    <option value="{{ $claimStage->value }}" @selected(old('claim_stage', $post?->claim_stage?->value) === $claimStage->value)>
                        {{ $claimStage->label() }}
                    </option>
                @endforeach
            </select>
            @error('claim_stage')
                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-slate-800">Publication status</label>
            <select id="status" name="status" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $post?->status?->value ?? 'draft') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="image" class="block text-sm font-semibold text-slate-800">Supporting image <span class="font-normal text-slate-500">(optional)</span></label>
        <input
            id="image"
            name="image"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            @if ($errors->has('image')) aria-invalid="true" aria-describedby="image-error" @endif
            class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-950 file:px-4 file:py-3 file:font-semibold file:text-white hover:file:bg-slate-800"
        >
        @error('image')
            <p id="image-error" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-slate-600">JPEG, PNG, or WebP up to 8 MB and 8000 × 8000 pixels. Do not upload claim documents, identifying details, or sensitive information.</p>

        @if ($post?->original_image_path)
            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                @if ($post->processed_image_path)
                    <img src="{{ $post->processedImageUrl() }}" alt="" class="max-h-48 rounded-lg object-cover">
                @else
                    <p class="text-sm font-medium text-slate-600">The current image is waiting to be processed.</p>
                @endif

                <label class="mt-3 flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image')) class="rounded border-slate-300 text-red-700 focus:ring-red-600">
                    Remove the current image
                </label>
                @error('remove_image')
                    <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="rounded-xl bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
            {{ $submitLabel }}
        </button>
        <a href="{{ $post ? route('posts.show', $post) : route('posts.index') }}" class="font-semibold text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-950">
            Cancel
        </a>
    </div>
</div>
