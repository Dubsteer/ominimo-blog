@csrf

@if ($method !== 'POST')
    @method($method)
@endif

@php($cancelUrl = $post ? route('posts.show', $post) : route('posts.index'))

<div class='space-y-8'>
    @if ($errors->any())
        <x-notice tone='danger' role='alert'>Some details need your attention. Review the highlighted fields below.</x-notice>
    @endif

    <fieldset class='space-y-6'>
        <legend class='text-xl font-extrabold tracking-tight'>Your story</legend>

        <x-form-field
            name='title'
            label='Title'
            :value='$post?->title'
            help='Make it specific enough to help someone scanning the community.'
            :required='true'
            maxlength='160'
            autofocus
        />

        <div>
            <label for='content' class='form-label'>Experience or question</label>
            <textarea
                id='content'
                name='content'
                rows='12'
                maxlength='50000'
                required
                @if ($errors->has('content')) aria-invalid='true' aria-describedby='content-help content-error' @else aria-describedby='content-help' @endif
                class='form-control min-h-72 resize-y leading-7'
            >{{ old('content', $post?->content) }}</textarea>
            <p id='content-help' class='form-help'>Minimum 30 characters. Focus on the process and lesson learned; remove names, contact details, claim or policy numbers, payment data, and medical information.</p>
            @error('content')
                <p id='content-error' class='form-error'>{{ $message }}</p>
            @enderror
        </div>
    </fieldset>

    <fieldset class='border-t border-brand-100 pt-7'>
        <legend class='text-xl font-extrabold tracking-tight'>Journey and visibility</legend>
        <p class='mt-2 text-sm leading-6 text-ink-600'>Help readers understand where this story belongs and whether it is ready to publish.</p>

        <div class='mt-6 grid gap-6 sm:grid-cols-2'>
            <div>
                <label for='claim_stage' class='form-label'>Claim stage</label>
                <select id='claim_stage' name='claim_stage' required @if ($errors->has('claim_stage')) aria-invalid='true' aria-describedby='claim-stage-error' @endif class='form-control'>
                    <option value=''>Select a stage</option>
                    @foreach ($claimStages as $claimStage)
                        <option value='{{ $claimStage->value }}' @selected(old('claim_stage', $post?->claim_stage?->value) === $claimStage->value)>
                            {{ $claimStage->label() }}
                        </option>
                    @endforeach
                </select>
                @error('claim_stage')
                    <p id='claim-stage-error' class='form-error'>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for='status' class='form-label'>Publication status</label>
                <select id='status' name='status' required @if ($errors->has('status')) aria-invalid='true' aria-describedby='status-error' @endif class='form-control'>
                    @foreach ($statuses as $status)
                        <option value='{{ $status->value }}' @selected(old('status', $post?->status?->value ?? 'draft') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <p class='form-help'>Drafts are visible only to you and authorized moderators.</p>
                @error('status')
                    <p id='status-error' class='form-error'>{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class='border-t border-brand-100 pt-7'>
        <legend class='text-xl font-extrabold tracking-tight'>Supporting image <span class='font-medium text-ink-600'>(optional)</span></legend>
        <p class='mt-2 text-sm leading-6 text-ink-600'>Add a general illustration or photo only when it helps explain the story.</p>

        <div class='mt-6'>
            <label for='image' class='form-label'>Choose an image</label>
            <input
                id='image'
                name='image'
                type='file'
                accept='image/jpeg,image/png,image/webp'
                @if ($errors->has('image')) aria-invalid='true' aria-describedby='image-help image-error' @else aria-describedby='image-help' @endif
                class='form-control p-0 text-sm text-ink-600 file:mr-4 file:min-h-12 file:border-0 file:bg-brand-600 file:px-5 file:py-3 file:font-bold file:text-white hover:file:bg-brand-500'
            >
            <p id='image-help' class='form-help'>JPEG, PNG, or WebP up to 8 MB and 8000 × 8000 pixels. Never upload claim documents or identifying details.</p>
            @error('image')
                <p id='image-error' class='form-error'>{{ $message }}</p>
            @enderror

            @if ($post?->original_image_path)
                <div class='mt-5 rounded-2xl border border-brand-100 bg-brand-50/60 p-4'>
                    @if ($post->processed_image_path)
                        <img src='{{ $post->processedImageUrl() }}' alt='' class='max-h-56 rounded-xl object-cover'>
                    @else
                        <x-notice tone='info' role='status'>The current image is waiting to be processed.</x-notice>
                    @endif

                    <label class='mt-4 flex min-h-11 cursor-pointer items-center gap-3 text-sm font-bold text-red-800'>
                        <input type='checkbox' name='remove_image' value='1' @checked(old('remove_image')) class='size-5 rounded-md border-red-300 text-red-700 focus:ring-red-300'>
                        Remove the current image
                    </label>
                    @error('remove_image')
                        <p class='form-error'>{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </fieldset>

    <div class='flex flex-wrap items-center gap-3 border-t border-brand-100 pt-7'>
        <x-button type='submit' size='lg'>{{ $submitLabel }}</x-button>
        <x-button :href='$cancelUrl' variant='quiet' size='lg'>Cancel</x-button>
    </div>
</div>
