@extends('layouts.app')
@section('title', 'Edit Patient')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('patients.index') }}" style="color:var(--clr-green);">Patients</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('patients.show', $patient) }}" style="color:var(--clr-green);">{{ $patient->full_name }}</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Edit</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Edit Patient</h1>
</div>

<form method="POST" action="{{ route('patients.update', $patient) }}" novalidate>
  @csrf @method('PUT')

  <div class="hmis-card" style="margin-bottom:var(--space-5);">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">Demographics</h2>
    </div>
    <div class="hmis-card__body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);">
        @foreach([
          ['first_name','First Name','text',true],
          ['middle_name','Middle Name','text',false],
          ['last_name','Last Name','text',true],
          ['date_of_birth','Date of Birth','date',false],
          ['national_id','National ID','text',false],
          ['phone_primary','Primary Phone','tel',false],
          ['phone_secondary','Secondary Phone','tel',false],
          ['email','Email','email',false],
          ['county','County','text',false],
          ['next_of_kin_name','Next of Kin Name','text',false],
          ['next_of_kin_phone','Next of Kin Phone','tel',false],
          ['next_of_kin_relation','Relationship','text',false],
        ] as [$field,$label,$type,$required])
          <div class="hmis-form-group">
            <label for="{{ $field }}" class="hmis-label">
              {{ $label }} @if($required)<span class="required">*</span>@endif
            </label>
            <input type="{{ $type }}" id="{{ $field }}" name="{{ $field }}"
                   class="hmis-input {{ $errors->has($field) ? 'is-invalid' : '' }}"
                   value="{{ old($field, $patient->$field instanceof \Carbon\Carbon ? $patient->$field->format('Y-m-d') : $patient->$field) }}"
                   {{ $required ? 'required' : '' }}>
            @error($field)<span class="hmis-error">{{ $message }}</span>@enderror
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div style="display:flex;gap:var(--space-3);">
    <button type="submit" class="btn-hmis-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
    <a href="{{ route('patients.show', $patient) }}" class="btn-hmis-secondary"><i class="bi bi-x"></i> Cancel</a>
  </div>
</form>
@endsection