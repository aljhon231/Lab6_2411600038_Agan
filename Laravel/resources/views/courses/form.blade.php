<div class="form-grid">
    <div class="field">
        <label for="name">Course name</label>
        <input id="name" name="name" value="{{ old('name', $course->name) }}" required maxlength="160">
    </div>
    <div class="field">
        <label for="code">Course code</label>
        <input id="code" name="code" value="{{ old('code', $course->code) }}" required maxlength="40">
    </div>
    <div class="field">
        <label for="category">Category</label>
        <input id="category" name="category" value="{{ old('category', $course->category) }}" required maxlength="100" placeholder="e.g. Database">
    </div>
    <div class="field">
        <label for="instructor">Instructor</label>
        <input id="instructor" name="instructor" value="{{ old('instructor', $course->instructor) }}" maxlength="120">
    </div>
    @if($course->exists)
        <div class="field">
            <label for="maximum_slots">Maximum slots</label>
            <input id="maximum_slots" name="maximum_slots" type="number" min="1" max="10000" value="{{ old('maximum_slots', $course->maximum_slots) }}" required>
            <small>Changing available slots is recorded separately as an adjustment.</small>
        </div>
        <div class="field">
            <label for="minimum_slots">Few-slots alert threshold</label>
            <input id="minimum_slots" name="minimum_slots" type="number" min="0" value="{{ old('minimum_slots', $course->minimum_slots) }}" required>
        </div>
    @else
        <div class="field">
            <label for="maximum_slots">Starting slots</label>
            <input id="maximum_slots" name="maximum_slots" type="number" min="1" max="10000" value="{{ old('maximum_slots', 30) }}" required>
            <small>New courses start with all their seats available.</small>
        </div>
        <div class="field">
            <label for="minimum_slots">Few-slots alert threshold</label>
            <input id="minimum_slots" name="minimum_slots" type="number" min="0" value="{{ old('minimum_slots', 5) }}" required>
        </div>
    @endif
    <div class="field field-wide">
        <label for="description">Description <span class="muted">(optional)</span></label>
        <textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $course->description) }}</textarea>
    </div>
</div>
