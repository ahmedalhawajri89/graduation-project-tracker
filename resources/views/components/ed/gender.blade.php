{{-- الجنس: زرّان مجزّآن بدل قائمة منسدلة لخيارين --}}
<div class="ed-field" data-ed-wrap="gender">
    <span class="form-label required" id="ed-gender-label">
        الجنس
        <span class="ed-changed-dot" title="تغيّر" aria-hidden="true"></span>
    </span>
    <div class="ed-segmented" role="radiogroup" aria-labelledby="ed-gender-label">
        <label>
            <input type="radio" name="gender" value="male" required>
            <span><i class="ti ti-gender-male" aria-hidden="true"></i> ذكر</span>
        </label>
        <label>
            <input type="radio" name="gender" value="female">
            <span><i class="ti ti-gender-female" aria-hidden="true"></i> أنثى</span>
        </label>
    </div>
    @if (old('id') && $errors->has('gender'))
        <div class="invalid-feedback d-block">{{ $errors->first('gender') }}</div>
    @endif
</div>
