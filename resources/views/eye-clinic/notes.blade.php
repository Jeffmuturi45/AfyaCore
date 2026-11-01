@extends('layouts.app')
@section('title', 'Eye Clinic Notes')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('eye-clinic.index') }}" style="color:var(--clr-green);">Eye Clinic</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">{{ $encounter->encounter_number }}</li>
@endsection

@section('content')
{{-- Patient Header --}}
<div class="hmis-patient-header" style="margin-bottom:var(--space-5);">
  <div class="hmis-patient-header__avatar"><i class="bi bi-person-fill"></i></div>
  <div style="flex:1;">
    <div class="hmis-patient-header__name">{{ $encounter->patient->full_name }}</div>
    <div class="hmis-patient-header__meta">
      <span><i class="bi bi-credit-card"></i> {{ $branchRecord?->mrn ?? '—' }}</span>
      @if($encounter->patient->date_of_birth)
        <span><i class="bi bi-calendar3"></i> {{ $encounter->patient->age }}</span>
      @endif
      <span><i class="bi bi-telephone"></i> {{ $encounter->patient->phone_primary ?? '—' }}</span>
    </div>
  </div>
  <div>
    @php $badges = ['in_progress'=>'amber','consultation_complete'=>'green']; @endphp
    <span class="hmis-badge hmis-badge--{{ $badges[$encounter->status] ?? 'grey' }}" style="font-size:var(--font-size-sm);padding:4px 12px;">
      {{ ucfirst(str_replace('_',' ',$encounter->status)) }}
    </span>
  </div>
</div>

@if($eyeEnc?->is_finalized)
  <div class="hmis-alert hmis-alert--success" style="margin-bottom:var(--space-5);">
    <i class="bi bi-lock-fill"></i>
    <div>Notes finalized at {{ $eyeEnc->finalized_at?->format('H:i, d M Y') }}. To amend, contact the branch administrator.</div>
  </div>
@endif

<form method="POST" action="{{ route('eye-clinic.notes.update', $encounter) }}">
  @csrf @method('PUT')

  {{-- History --}}
  <div class="hmis-card" style="margin-bottom:var(--space-4);">
    <div class="hmis-card__header"><h2 class="hmis-card__title"><i class="bi bi-journal-text text-green"></i> History</h2></div>
    <div class="hmis-card__body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="hmis-form-group">
          <label class="hmis-label">Chief Complaint</label>
          <textarea name="chief_complaint" class="hmis-input" rows="3" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>{{ old('chief_complaint', $eyeEnc?->chief_complaint) }}</textarea>
        </div>
        <div class="hmis-form-group">
          <label class="hmis-label">Ocular History</label>
          <textarea name="history" class="hmis-input" rows="3" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>{{ old('history', $eyeEnc?->history) }}</textarea>
        </div>
        <div class="hmis-form-group" style="grid-column:1/-1;">
          <label class="hmis-label">General Health Notes</label>
          <textarea name="general_health_notes" class="hmis-input" rows="2" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>{{ old('general_health_notes', $eyeEnc?->general_health_notes) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  {{-- Visual Acuity --}}
  <div class="hmis-card" style="margin-bottom:var(--space-4);">
    <div class="hmis-card__header"><h2 class="hmis-card__title"><i class="bi bi-eye text-green"></i> Visual Acuity</h2></div>
    <div class="hmis-card__body">
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:var(--font-size-sm);">
          <thead>
            <tr style="background:var(--clr-grey-100);">
              <th style="padding:var(--space-2) var(--space-3);text-align:left;font-size:var(--font-size-xs);color:var(--clr-grey-600);">Eye</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Unaided Distance</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Unaided Near</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Aided Distance</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Aided Near</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Pinhole</th>
            </tr>
          </thead>
          <tbody>
            @foreach(['RE'=>'Right Eye','LE'=>'Left Eye'] as $code=>$label)
              @php $va = $eyeEnc?->visualAcuity->firstWhere('eye', $code); @endphp
              <tr style="border-bottom:1px solid var(--clr-grey-200);">
                <td style="padding:var(--space-2) var(--space-3);font-weight:600;">
                  <input type="hidden" name="va[{{ $loop->index }}][eye]" value="{{ $code }}">
                  {{ $label }}
                </td>
                @foreach(['unaided_distance','unaided_near','aided_distance','aided_near','pinhole'] as $field)
                  <td style="padding:var(--space-1) var(--space-2);">
                    <input type="text" name="va[{{ $loop->parent->index }}][{{ $field }}]"
                           class="hmis-input" style="width:90px;text-align:center;"
                           value="{{ old("va.{$loop->parent->index}.{$field}", $va?->$field) }}"
                           placeholder="6/—" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Refraction --}}
  <div class="hmis-card" style="margin-bottom:var(--space-4);">
    <div class="hmis-card__header"><h2 class="hmis-card__title"><i class="bi bi-eyeglasses text-green"></i> Refraction</h2></div>
    <div class="hmis-card__body">
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:var(--font-size-sm);">
          <thead>
            <tr style="background:var(--clr-grey-100);">
              <th style="padding:var(--space-2) var(--space-3);text-align:left;font-size:var(--font-size-xs);color:var(--clr-grey-600);">Eye</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Sphere</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Cylinder</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Axis</th>
              <th style="padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-600);">Addition</th>
            </tr>
          </thead>
          <tbody>
            @foreach(['RE'=>'Right Eye','LE'=>'Left Eye'] as $code=>$label)
              @php $rx = $eyeEnc?->refractionRecords->firstWhere('eye', $code); @endphp
              <tr style="border-bottom:1px solid var(--clr-grey-200);">
                <td style="padding:var(--space-2) var(--space-3);font-weight:600;">
                  <input type="hidden" name="rx[{{ $loop->index }}][eye]" value="{{ $code }}">
                  {{ $label }}
                </td>
                @foreach(['sphere','cylinder','axis','addition'] as $field)
                  <td style="padding:var(--space-1) var(--space-2);">
                    <input type="number" step="0.25" name="rx[{{ $loop->parent->index }}][{{ $field }}]"
                           class="hmis-input" style="width:90px;text-align:center;"
                           value="{{ old("rx.{$loop->parent->index}.{$field}", $rx?->$field) }}"
                           {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Optical Prescription --}}
  <div class="hmis-card" style="margin-bottom:var(--space-4);">
    <div class="hmis-card__header"><h2 class="hmis-card__title"><i class="bi bi-file-medical text-green"></i> Optical Prescription</h2></div>
    <div class="hmis-card__body">
      @php $rx = $eyeEnc?->opticalPrescriptions->first(); @endphp
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--space-4);">
        <div class="hmis-form-group">
          <label class="hmis-label">Prescription Type</label>
          <select name="prescription_type" class="hmis-select" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
            <option value="">— None —</option>
            @foreach(['distance'=>'Distance','near'=>'Near','bifocal'=>'Bifocal','progressive'=>'Progressive','safety'=>'Safety'] as $v=>$l)
              <option value="{{ $v }}" {{ old('prescription_type', $rx?->prescription_type)==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="hmis-form-group">
          <label class="hmis-label">PD Distance (mm)</label>
          <input type="number" step="0.5" name="pd_distance" class="hmis-input"
                 value="{{ old('pd_distance', $rx?->pd_distance) }}" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
        </div>
        <div class="hmis-form-group">
          <label class="hmis-label">PD Near (mm)</label>
          <input type="number" step="0.5" name="pd_near" class="hmis-input"
                 value="{{ old('pd_near', $rx?->pd_near) }}" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-5);margin-top:var(--space-4);">
        @foreach(['re'=>'Right Eye (RE)','le'=>'Left Eye (LE)'] as $eye=>$label)
          <div>
            <div style="font-weight:var(--font-weight-semibold);margin-bottom:var(--space-3);color:var(--clr-charcoal);">{{ $label }}</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-3);">
              @foreach(['sphere'=>'Sph','cylinder'=>'Cyl','axis'=>'Axis','addition'=>'Add'] as $field=>$flabel)
                <div class="hmis-form-group">
                  <label class="hmis-label">{{ $flabel }}</label>
                  <input type="number" step="0.25" name="{{ $eye }}_{{ $field }}" class="hmis-input"
                         value="{{ old("{$eye}_{$field}", $rx?->{"{$eye}_{$field}"}) }}"
                         {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);margin-top:var(--space-4);">
        <div class="hmis-form-group">
          <label class="hmis-label">Frame Recommendation</label>
          <textarea name="frame_recommendation" class="hmis-input" rows="2" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>{{ old('frame_recommendation', $rx?->frame_recommendation) }}</textarea>
        </div>
        <div class="hmis-form-group">
          <label class="hmis-label">Lens Recommendation</label>
          <textarea name="lens_recommendation" class="hmis-input" rows="2" {{ $eyeEnc?->is_finalized ? 'disabled' : '' }}>{{ old('lens_recommendation', $rx?->lens_recommendation) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  @if(!$eyeEnc?->is_finalized)
    <div style="display:flex;gap:var(--space-3);">
      <button type="submit" class="btn-hmis-secondary">
        <i class="bi bi-floppy"></i> Save Draft
      </button>
      <button type="submit" name="finalize" value="1" class="btn-hmis-primary"
              onclick="return confirm('Finalize notes? This cannot be undone.')">
        <i class="bi bi-check-circle"></i> Finalize & Complete
      </button>
      <a href="{{ route('encounters.show', $encounter) }}" class="btn-hmis-secondary">
        <i class="bi bi-x"></i> Cancel
      </a>
    </div>
  @endif
</form>
@endsection