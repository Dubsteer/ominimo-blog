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

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="rounded-xl bg-slate-950 px-6 py-3 font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
            {{ $submitLabel }}
        </button>
        <a href="{{ $post ? route('posts.show', $post) : route('posts.index') }}" class="font-semibold text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-950">
            Cancel
        </a>
    </div>
</div>
