@extends('layouts.app')

@section('title', 'Consultation — ' . $encounter->patient->full_name)

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
  <li><span style="margin: 0 4px;">/</span></li>
  <li><a href="{{ route('consultations.index') }}">Consultations</a></li>
  <li><span style="margin: 0 4px;">/</span></li>
  <li style="color:var(--clr-grey-900);font-weight:var(--font-weight-medium);">{{ $encounter->patient->full_name }}</li>
@endsection

@section('content')
<div style="max-width:1200px;margin:0 auto;" x-data="consultationWorkspace()">
  {{-- TOP ENCOUNTER STATUS BAR --}}
  <div class="hmis-metric" style="padding:var(--space-4) var(--space-6);margin-bottom:var(--space-6);background:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <div style="width:44px;height:44px;border-radius:var(--radius-pill);background:var(--clr-green-light);color:var(--clr-green-dark);display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:1.1rem;">
          {{ strtoupper(substr($encounter->patient->first_name, 0, 1) . substr($encounter->patient->last_name, 0, 1)) }}
        </div>
        <div>
          <div style="display:flex;align-items:center;gap:var(--space-2);">
            <strong style="font-size:var(--font-size-lg);color:var(--clr-charcoal);">{{ $encounter->patient->full_name }}</strong>
            <span class="hmis-badge hmis-badge--green" style="font-family:monospace;">MRN: {{ $encounter->patient->mrn($encounter->branch_id) }}</span>
            @php
              $badgeClass = match($encounter->status) {
                'in_progress'           => 'hmis-badge--blue',
                'consultation_complete' => 'hmis-badge--green',
                'billed'                => 'hmis-badge--green',
                'closed'                => 'hmis-badge--grey',
                default                 => 'hmis-badge--amber',
              };
            @endphp
            <span class="hmis-badge {{ $badgeClass }}">
              {{ strtoupper(str_replace('_', ' ', $encounter->status)) }}
            </span>
          </div>
          <div style="font-size:var(--font-size-xs);color:var(--clr-grey-600);margin-top:2px;">
            {{ ucfirst($encounter->patient->gender ?? '') }}, {{ $encounter->patient->age ?? 'DOB: N/A' }} &bull;
            Phone: {{ $encounter->patient->phone_primary ?? 'N/A' }} &bull;
            Arrival: {{ $encounter->visit_date->format('d M Y') }} at {{ $encounter->arrival_time?->format('H:i') }}
          </div>
        </div>
      </div>

      <div style="display:flex;gap:var(--space-2);">
        <a href="{{ route('encounters.show', $encounter) }}" class="btn-hmis-secondary" style="font-size:var(--font-size-xs);">
          <i class="bi bi-eye"></i> View Full File
        </a>
        <a href="{{ route('consultations.index') }}" class="btn-hmis-secondary" style="font-size:var(--font-size-xs);">
          <i class="bi bi-x-lg"></i> Back to Queue
        </a>
      </div>
    </div>
  </div>

  {{-- 2-COLUMN CLINICAL LAYOUT --}}
  <div style="display:grid;grid-template-columns:320px 1fr;gap:var(--space-6);align-items:start;">
    {{-- LEFT SIDEBAR: Triage Vitals & Patient History --}}
    <div style="display:flex;flex-direction:column;gap:var(--space-5);">
      {{-- Vitals Summary Card --}}
      <div class="hmis-metric" style="padding:var(--space-5);">
        <h3 style="font-size:var(--font-size-sm);font-weight:var(--font-weight-semibold);text-transform:uppercase;letter-spacing:.05em;margin:0 0 var(--space-3);color:var(--clr-grey-600);border-bottom:1px solid var(--border-color);padding-bottom:var(--space-2);">
          <i class="bi bi-heart-pulse-fill" style="color:var(--clr-danger);"></i> Triage Vitals
        </h3>

        @if($encounter->triageRecord)
          @php $tr = $encounter->triageRecord; @endphp
          <div style="display:flex;flex-direction:column;gap:var(--space-2);font-size:var(--font-size-sm);">
            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">Blood Pressure:</span>
              <strong style="color:{{ ($tr->bp_systolic >= 140 || $tr->bp_diastolic >= 90) ? 'var(--clr-danger)' : 'inherit' }};">
                {{ $tr->bp ?? '—' }}
              </strong>
            </div>

            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">Temperature:</span>
              <strong style="color:{{ $tr->temperature_c >= 38.0 ? 'var(--clr-danger)' : 'inherit' }};">
                {{ $tr->temperature_c ? $tr->temperature_c . ' °C' : '—' }}
              </strong>
            </div>

            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">Pulse Rate:</span>
              <strong>{{ $tr->pulse_bpm ? $tr->pulse_bpm . ' bpm' : '—' }}</strong>
            </div>

            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">SpO2 Saturation:</span>
              <strong style="color:{{ $tr->spo2_percent < 95 ? 'var(--clr-danger)' : 'inherit' }};">
                {{ $tr->spo2_percent ? $tr->spo2_percent . '%' : '—' }}
              </strong>
            </div>

            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">Weight & Height:</span>
              <span>{{ $tr->weight_kg ? $tr->weight_kg . ' kg' : '—' }} / {{ $tr->height_cm ? $tr->height_cm . ' cm' : '—' }}</span>
            </div>

            <div style="display:flex;justify-content:space-between;padding:var(--space-1) 0;border-bottom:1px solid var(--clr-grey-100);">
              <span style="color:var(--clr-grey-600);">BMI:</span>
              <strong style="color:var(--clr-green-dark);">{{ $tr->bmi ?? '—' }}</strong>
            </div>

            @if($tr->notes)
              <div style="margin-top:var(--space-2);background:var(--clr-grey-100);padding:var(--space-2);border-radius:var(--radius-sm);font-size:var(--font-size-xs);">
                <strong>Nurse Note:</strong> {{ $tr->notes }}
              </div>
            @endif

            <div style="font-size:10px;color:var(--clr-grey-500);text-align:right;margin-top:var(--space-1);">
              Taken by {{ $tr->recordedBy?->name ?? 'Nurse' }} on {{ $tr->recorded_at?->format('H:i') }}
            </div>
          </div>
        @else
          <div style="color:var(--clr-grey-500);font-size:var(--font-size-xs);text-align:center;padding:var(--space-3);">
            No vitals recorded for this visit.
          </div>
        @endif
      </div>

      {{-- Chief Complaint Card --}}
      <div class="hmis-metric" style="padding:var(--space-5);">
        <h3 style="font-size:var(--font-size-sm);font-weight:var(--font-weight-semibold);text-transform:uppercase;letter-spacing:.05em;margin:0 0 var(--space-2);color:var(--clr-grey-600);">
          <i class="bi bi-chat-left-quote"></i> Presenting Complaint
        </h3>
        <p style="margin:0;font-size:var(--font-size-sm);color:var(--clr-grey-900);line-height:1.5;">
          {{ $encounter->chief_complaint ?: 'No chief complaint recorded at check-in.' }}
        </p>
      </div>

      {{-- Previous Visits History --}}
      <div class="hmis-metric" style="padding:var(--space-5);">
        <h3 style="font-size:var(--font-size-sm);font-weight:var(--font-weight-semibold);text-transform:uppercase;letter-spacing:.05em;margin:0 0 var(--space-3);color:var(--clr-grey-600);border-bottom:1px solid var(--border-color);padding-bottom:var(--space-2);">
          <i class="bi bi-clock-history"></i> Past Encounters
        </h3>

        @forelse($patientHistory as $past)
          @php $pastNote = $past->currentNote(); @endphp
          <div style="padding:var(--space-2) 0;border-bottom:1px solid var(--clr-grey-100);font-size:var(--font-size-xs);">
            <div style="display:flex;justify-content:space-between;color:var(--clr-grey-600);">
              <span>{{ $past->visit_date->format('d M Y') }}</span>
              <span class="hmis-badge hmis-badge--grey">{{ strtoupper($past->encounter_type) }}</span>
            </div>
            @if($pastNote && $pastNote->diagnosis)
              <div style="font-weight:bold;color:var(--clr-grey-900);margin-top:2px;">
                {{ $pastNote->diagnosis }}
              </div>
            @endif
            @if($past->triageRecord)
              <div style="color:var(--clr-grey-500);margin-top:1px;">
                BP: {{ $past->triageRecord->bp ?? '—' }} | T: {{ $past->triageRecord->temperature_c ? $past->triageRecord->temperature_c . '°C' : '—' }}
              </div>
            @endif
          </div>
        @empty
          <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);text-align:center;padding:var(--space-2);">
            First recorded visit for this patient.
          </div>
        @endforelse
      </div>
    </div>

    {{-- RIGHT WORKSPACE: Tabs for Consultation Notes, Prescriptions, Referrals, Versions --}}
    <div>
      {{-- Workspace Tab Headers --}}
      <div style="display:flex;border-bottom:2px solid var(--border-color);margin-bottom:var(--space-5);gap:var(--space-2);">
        <button type="button"
                @click="activeTab = 'notes'"
                :style="activeTab === 'notes' ? 'border-bottom:3px solid var(--clr-green);color:var(--clr-green-dark);font-weight:bold;' : 'color:var(--clr-grey-600);'"
                style="background:none;border:none;padding:var(--space-2) var(--space-4);font-size:var(--font-size-sm);cursor:pointer;display:inline-flex;align-items:center;gap:var(--space-2);">
          <i class="bi bi-journal-medical"></i> Clinical Documentation
        </button>

        <button type="button"
                @click="activeTab = 'prescriptions'"
                :style="activeTab === 'prescriptions' ? 'border-bottom:3px solid var(--clr-green);color:var(--clr-green-dark);font-weight:bold;' : 'color:var(--clr-grey-600);'"
                style="background:none;border:none;padding:var(--space-2) var(--space-4);font-size:var(--font-size-sm);cursor:pointer;display:inline-flex;align-items:center;gap:var(--space-2);">
          <i class="bi bi-capsule"></i> Prescriptions
          @if($encounter->prescriptions->isNotEmpty())
            <span class="hmis-badge hmis-badge--green">{{ $encounter->prescriptions->count() }}</span>
          @endif
        </button>

        <button type="button"
                @click="activeTab = 'referrals'"
                :style="activeTab === 'referrals' ? 'border-bottom:3px solid var(--clr-green);color:var(--clr-green-dark);font-weight:bold;' : 'color:var(--clr-grey-600);'"
                style="background:none;border:none;padding:var(--space-2) var(--space-4);font-size:var(--font-size-sm);cursor:pointer;display:inline-flex;align-items:center;gap:var(--space-2);">
          <i class="bi bi-box-arrow-up-right"></i> Referral Out
          @if($encounter->referrals->isNotEmpty())
            <span class="hmis-badge hmis-badge--blue">{{ $encounter->referrals->count() }}</span>
          @endif
        </button>

        @if($encounter->clinicalNotes->count() > 1)
          <button type="button"
                  @click="activeTab = 'versions'"
                  :style="activeTab === 'versions' ? 'border-bottom:3px solid var(--clr-green);color:var(--clr-green-dark);font-weight:bold;' : 'color:var(--clr-grey-600);'"
                  style="background:none;border:none;padding:var(--space-2) var(--space-4);font-size:var(--font-size-sm);cursor:pointer;display:inline-flex;align-items:center;gap:var(--space-2);">
            <i class="bi bi-clock-history"></i> Version History ({{ $encounter->clinicalNotes->count() }})
          </button>
        @endif
      </div>

      {{-- TAB 1: CLINICAL NOTES FORM --}}
      <div x-show="activeTab === 'notes'" x-cloak>
        @if($currentNote && $currentNote->is_finalized)
          {{-- Finalized Note Notice (AC-17) --}}
          <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:var(--radius-lg);padding:var(--space-4);margin-bottom:var(--space-5);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div style="display:flex;align-items:center;gap:var(--space-2);color:var(--clr-green-dark);font-weight:bold;">
                <i class="bi bi-lock-fill"></i>
                <span>Clinical Notes Finalized (Version {{ $currentNote->version }})</span>
              </div>
              <button type="button" @click="amendModalOpen = true" class="btn-hmis-secondary" style="font-size:var(--font-size-xs);">
                <i class="bi bi-pencil-square"></i> Amend Finalized Note
              </button>
            </div>
            <p style="margin:var(--space-2) 0 0;font-size:var(--font-size-xs);color:var(--clr-grey-700);">
              This consultation record was finalized on {{ $currentNote->finalized_at?->format('d M Y, H:i') }} by {{ $currentNote->authoredBy?->name }}.
              In accordance with Kenya DHA and HMIS compliance, finalized clinical notes cannot be overwritten; amendments create an append-only version with an audited change reason.
            </p>
          </div>
        @endif

        {{-- Consultation Form: can save draft or complete --}}
        <form method="POST" id="consultation-form">
          @csrf

          <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
            <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);">
              1. History of Presenting Illness
            </h3>

            <div class="hmis-form-group">
              <label class="hmis-label" for="presenting_complaints">Presenting Complaints & Duration</label>
              <textarea name="presenting_complaints"
                        id="presenting_complaints"
                        class="hmis-input"
                        rows="3"
                        {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                        placeholder="e.g. Throbbing frontal headache x 4 days, low-grade fever, nausea...">{{ old('presenting_complaints', $currentNote?->presenting_complaints ?? $encounter->chief_complaint) }}</textarea>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="history_of_illness">History of Present Illness (HPI)</label>
              <textarea name="history_of_illness"
                        id="history_of_illness"
                        class="hmis-input"
                        rows="3"
                        {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                        placeholder="Detailed chronological narrative of the present illness...">{{ old('history_of_illness', $currentNote?->history_of_illness) }}</textarea>
            </div>
          </div>

          {{-- Medical Background --}}
          <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
            <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);">
              2. Medical & Surgical Background
            </h3>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
              <div class="hmis-form-group">
                <label class="hmis-label" for="past_medical_history">Past Medical History (PMH)</label>
                <textarea name="past_medical_history"
                          id="past_medical_history"
                          class="hmis-input"
                          rows="2"
                          {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                          placeholder="e.g. Hypertension (5 yrs), Asthma...">{{ old('past_medical_history', $currentNote?->past_medical_history) }}</textarea>
              </div>

              <div class="hmis-form-group">
                <label class="hmis-label" for="past_surgical_history">Past Surgical History (PSH)</label>
                <textarea name="past_surgical_history"
                          id="past_surgical_history"
                          class="hmis-input"
                          rows="2"
                          {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                          placeholder="e.g. Appendectomy 2018...">{{ old('past_surgical_history', $currentNote?->past_surgical_history) }}</textarea>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);margin-top:var(--space-2);">
              <div class="hmis-form-group">
                <label class="hmis-label" for="drug_history">Drug History & Known Allergies</label>
                <textarea name="drug_history"
                          id="drug_history"
                          class="hmis-input"
                          rows="2"
                          {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                          placeholder="Current medications, Penicillin/Sulfa allergies...">{{ old('drug_history', $currentNote?->drug_history) }}</textarea>
              </div>

              <div class="hmis-form-group">
                <label class="hmis-label" for="family_history">Family & Social History</label>
                <textarea name="family_history"
                          id="family_history"
                          class="hmis-input"
                          rows="2"
                          {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                          placeholder="Diabetes/Hypertension in family, smoking/alcohol...">{{ old('family_history', $currentNote?->family_history) }}</textarea>
              </div>
            </div>
          </div>

          {{-- Physical Examination --}}
          <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
            <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);">
              3. Physical Examination Findings
            </h3>

            <div class="hmis-form-group">
              <label class="hmis-label" for="examination_findings">General & Systemic Physical Examination</label>
              <textarea name="examination_findings"
                        id="examination_findings"
                        class="hmis-input"
                        rows="4"
                        {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                        placeholder="General appearance, HEENT, Respiratory, Cardiovascular, Abdomen, CNS, Extremities...">{{ old('examination_findings', $currentNote?->examination_findings) }}</textarea>
            </div>
          </div>

          {{-- Assessment & Treatment Plan --}}
          <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
            <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);">
              4. Clinical Diagnosis & Plan
            </h3>

            <div class="hmis-form-group">
              <label class="hmis-label" for="diagnosis">Working / Final Diagnosis <span class="required">*</span></label>
              <input type="text"
                     name="diagnosis"
                     id="diagnosis"
                     class="hmis-input @error('diagnosis') is-invalid @enderror"
                     value="{{ old('diagnosis', $currentNote?->diagnosis) }}"
                     {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                     placeholder="e.g. Acute Upper Respiratory Tract Infection (URTI)"
                     required>
              @error('diagnosis') <div class="hmis-error">{{ $message }}</div> @enderror
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="treatment_plan">Treatment Plan & Non-Pharmacological Management</label>
              <textarea name="treatment_plan"
                        id="treatment_plan"
                        class="hmis-input"
                        rows="3"
                        {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                        placeholder="Clinical interventions, oral rehydration, bed rest, nursing orders...">{{ old('treatment_plan', $currentNote?->treatment_plan) }}</textarea>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="follow_up_instructions">Follow-up Instructions & Return Date</label>
              <input type="text"
                     name="follow_up_instructions"
                     id="follow_up_instructions"
                     class="hmis-input"
                     value="{{ old('follow_up_instructions', $currentNote?->follow_up_instructions) }}"
                     {{ ($currentNote && $currentNote->is_finalized) ? 'readonly' : '' }}
                     placeholder="e.g. Review in 5 days or sooner if fever persists">
            </div>
          </div>

          {{-- Action Buttons --}}
          @if(! $currentNote || ! $currentNote->is_finalized)
            <div style="display:flex;justify-content:flex-end;gap:var(--space-3);margin-bottom:var(--space-8);">
              <button type="submit"
                      formaction="{{ route('consultations.draft', $encounter) }}"
                      class="btn-hmis-secondary">
                <i class="bi bi-save"></i> Save Draft
              </button>
              <button type="submit"
                      formaction="{{ route('consultations.complete', $encounter) }}"
                      class="btn-hmis-primary"
                      style="padding:var(--space-3) var(--space-6);font-size:var(--font-size-base);">
                <i class="bi bi-check2-all"></i> Complete Consultation
              </button>
            </div>
          @endif
        </form>
      </div>

      {{-- TAB 2: PRESCRIPTIONS --}}
      <div x-show="activeTab === 'prescriptions'" x-cloak>
        <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
          <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);display:flex;align-items:center;gap:var(--space-2);">
            <i class="bi bi-capsule"></i> Prescribe Medications
          </h3>

          <form method="POST" action="{{ route('prescriptions.store', $encounter) }}">
            @csrf

            {{-- Dynamic Items Table --}}
            <div style="margin-bottom:var(--space-4);">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-3);">
                <span style="font-size:var(--font-size-sm);font-weight:var(--font-weight-semibold);color:var(--clr-grey-800);">Medication Items</span>
                <button type="button" @click="addMedicationItem()" class="btn-hmis-secondary" style="font-size:var(--font-size-xs);">
                  <i class="bi bi-plus-circle"></i> Add Medication
                </button>
              </div>

              <div style="display:flex;flex-direction:column;gap:var(--space-3);">
                <template x-for="(item, index) in prescriptionItems" :key="index">
                  <div style="background:var(--clr-grey-100);padding:var(--space-3);border-radius:var(--radius-md);border:1px solid var(--border-color);">
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:var(--space-3);align-items:end;">
                      <div class="hmis-form-group" style="margin:0;">
                        <label class="hmis-label" style="font-size:var(--font-size-xs);">Drug / Medication</label>
                        <select :name="'items[' + index + '][stock_item_id]'"
                                class="hmis-select"
                                x-model="item.stock_item_id"
                                @change="onDrugSelect(index)"
                                required>
                          <option value="">-- Select Medication --</option>
                          @foreach($availableMedications as $med)
                            <option value="{{ $med->id }}" data-name="{{ $med->name }}" data-unit="{{ $med->unit_of_measure }}">
                              {{ $med->name }} ({{ $med->generic_name }})
                            </option>
                          @endforeach
                        </select>
                        <input type="hidden" :name="'items[' + index + '][drug_name]'" :value="item.drug_name">
                      </div>

                      <div class="hmis-form-group" style="margin:0;">
                        <label class="hmis-label" style="font-size:var(--font-size-xs);">Dose</label>
                        <input type="text"
                               :name="'items[' + index + '][dose]'"
                               class="hmis-input"
                               placeholder="e.g. 500mg"
                               x-model="item.dose"
                               required>
                      </div>

                      <div class="hmis-form-group" style="margin:0;">
                        <label class="hmis-label" style="font-size:var(--font-size-xs);">Frequency</label>
                        <select :name="'items[' + index + '][frequency]'" class="hmis-select" x-model="item.frequency" required>
                          <option value="OD">OD (Once daily)</option>
                          <option value="BD">BD (Twice daily)</option>
                          <option value="TDS">TDS (Three times daily)</option>
                          <option value="QDS">QDS (Four times daily)</option>
                          <option value="PRN">PRN (As needed)</option>
                          <option value="STAT">STAT (Immediately)</option>
                        </select>
                      </div>

                      <div class="hmis-form-group" style="margin:0;">
                        <label class="hmis-label" style="font-size:var(--font-size-xs);">Total Qty</label>
                        <input type="number"
                               :name="'items[' + index + '][quantity_prescribed]'"
                               class="hmis-input"
                               placeholder="Qty"
                               x-model="item.quantity_prescribed"
                               min="1"
                               required>
                      </div>

                      <div>
                        <button type="button" @click="removeMedicationItem(index)" class="btn-hmis-danger" style="padding:6px 10px;" title="Remove">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-3);margin-top:var(--space-2);">
                      <div>
                        <input type="text"
                               :name="'items[' + index + '][duration]'"
                               class="hmis-input"
                               placeholder="Duration (e.g. 5 days)"
                               x-model="item.duration"
                               style="font-size:var(--font-size-xs);">
                      </div>
                      <div>
                        <input type="text"
                               :name="'items[' + index + '][instructions]'"
                               class="hmis-input"
                               placeholder="Instructions (e.g. Take with meals)"
                               x-model="item.instructions"
                               style="font-size:var(--font-size-xs);">
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="rx_notes">Pharmacist Instructions / Remarks</label>
              <textarea name="notes" id="rx_notes" class="hmis-input" rows="2" placeholder="e.g. Substitute with generic if unavailable, counsel patient on taking full course..."></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" class="btn-hmis-primary">
                <i class="bi bi-send-check"></i> Issue Prescription
              </button>
            </div>
          </form>
        </div>

        {{-- Existing Prescriptions List --}}
        @if($encounter->prescriptions->isNotEmpty())
          <div class="hmis-metric" style="padding:var(--space-5);">
            <h4 style="font-size:var(--font-size-sm);font-weight:bold;margin:0 0 var(--space-3);color:var(--clr-grey-800);">Prescription Orders for this Encounter</h4>
            @foreach($encounter->prescriptions as $rx)
              <div style="background:var(--clr-grey-100);padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-2);">
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-xs);color:var(--clr-grey-600);margin-bottom:var(--space-1);">
                  <span>Prescription #{{ $rx->id }} &bull; Prescribed by {{ $rx->prescribedBy?->name }}</span>
                  <span class="hmis-badge hmis-badge--green">{{ strtoupper($rx->status) }}</span>
                </div>
                <ul style="margin:0;padding-left:var(--space-4);font-size:var(--font-size-sm);">
                  @foreach($rx->items as $item)
                    <li>
                      <strong>{{ $item->drug_name }}</strong> &bull; {{ $item->dose }} &bull; {{ $item->frequency }} &bull; Qty: {{ $item->quantity_prescribed }}
                      @if($item->instructions)
                        <span style="font-size:var(--font-size-xs);color:var(--clr-grey-600);">({{ $item->instructions }})</span>
                      @endif
                    </li>
                  @endforeach
                </ul>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- TAB 3: REFERRAL OUT --}}
      <div x-show="activeTab === 'referrals'" x-cloak>
        <div class="hmis-metric" style="padding:var(--space-6);margin-bottom:var(--space-6);">
          <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-green-dark);display:flex;align-items:center;gap:var(--space-2);">
            <i class="bi bi-box-arrow-up-right"></i> Outgoing Clinical Referral
          </h3>

          <form method="POST" action="{{ route('referrals.store', $encounter) }}">
            @csrf

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-4);">
              <div class="hmis-form-group">
                <label class="hmis-label" for="destination">Destination Health Facility / Specialist <span class="required">*</span></label>
                <input type="text"
                       name="destination"
                       id="destination"
                       class="hmis-input"
                       placeholder="e.g. Nyeri County Referral Hospital / ENT Specialist"
                       required>
              </div>

              <div class="hmis-form-group">
                <label class="hmis-label" for="urgency">Urgency Level <span class="required">*</span></label>
                <select name="urgency" id="urgency" class="hmis-select" required>
                  <option value="routine" selected>Routine</option>
                  <option value="urgent">Urgent</option>
                  <option value="emergency">Emergency / Immediate</option>
                </select>
              </div>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="referral_reason">Reason for Referral <span class="required">*</span></label>
              <textarea name="reason"
                        id="referral_reason"
                        class="hmis-input"
                        rows="3"
                        placeholder="Clinical rationale for referral and required investigations/procedures..."
                        required></textarea>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label" for="clinical_summary">Summary of Findings & Treatment Provided</label>
              <textarea name="clinical_summary"
                        id="clinical_summary"
                        class="hmis-input"
                        rows="3"
                        placeholder="Summary of presentation, vital signs, physical exam, and medications administered prior to transfer..."></textarea>
            </div>

            <div class="hmis-form-group">
              <label style="display:flex;align-items:center;gap:var(--space-2);cursor:pointer;font-size:var(--font-size-sm);">
                <input type="checkbox" name="follow_up_required" value="1">
                <span>Follow-up tracking required after referral?</span>
              </label>
            </div>

            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" class="btn-hmis-primary">
                <i class="bi bi-check-lg"></i> Record Referral
              </button>
            </div>
          </form>
        </div>

        @if($encounter->referrals->isNotEmpty())
          <div class="hmis-metric" style="padding:var(--space-5);">
            <h4 style="font-size:var(--font-size-sm);font-weight:bold;margin:0 0 var(--space-3);color:var(--clr-grey-800);">Recorded Referrals</h4>
            @foreach($encounter->referrals as $ref)
              <div style="background:var(--clr-grey-100);padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-2);font-size:var(--font-size-sm);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <strong>{{ $ref->destination }}</strong>
                  <span class="hmis-badge {{ $ref->urgency === 'emergency' ? 'hmis-badge--red' : ($ref->urgency === 'urgent' ? 'hmis-badge--amber' : 'hmis-badge--blue') }}">
                    {{ strtoupper($ref->urgency) }}
                  </span>
                </div>
                <p style="margin:var(--space-2) 0;color:var(--clr-grey-800);font-size:var(--font-size-xs);">
                  {{ $ref->reason }}
                </p>
                <div style="font-size:10px;color:var(--clr-grey-500);">
                  Referred by {{ $ref->referredBy?->name }} on {{ $ref->created_at->format('d M Y, H:i') }}
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- TAB 4: VERSION HISTORY (AC-17) --}}
      @if($encounter->clinicalNotes->count() > 1)
        <div x-show="activeTab === 'versions'" x-cloak>
          <div class="hmis-metric" style="padding:var(--space-6);">
            <h3 style="font-size:var(--font-size-base);font-weight:var(--font-weight-semibold);margin:0 0 var(--space-4);color:var(--clr-charcoal);">
              <i class="bi bi-clock-history"></i> Clinical Note Revision History
            </h3>

            <div style="display:flex;flex-direction:column;gap:var(--space-4);">
              @foreach($encounter->clinicalNotes as $noteVer)
                <div style="border:1px solid {{ $noteVer->is_current ? 'var(--clr-green)' : 'var(--border-color)' }};border-radius:var(--radius-md);padding:var(--space-4);background:{{ $noteVer->is_current ? 'var(--clr-green-light)' : '#fff' }};">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-2);">
                    <div>
                      <strong>Version {{ $noteVer->version }}</strong>
                      @if($noteVer->is_current)
                        <span class="hmis-badge hmis-badge--green">ACTIVE CURRENT</span>
                      @else
                        <span class="hmis-badge hmis-badge--grey">SUPERSEDED</span>
                      @endif
                    </div>
                    <div style="font-size:var(--font-size-xs);color:var(--clr-grey-600);">
                      Created: {{ $noteVer->created_at->format('d M Y, H:i') }} by {{ $noteVer->authoredBy?->name }}
                    </div>
                  </div>

                  @if($noteVer->amended_by)
                    <div style="background:#fff8e1;border:1px solid #ffe082;padding:var(--space-2);border-radius:var(--radius-sm);font-size:var(--font-size-xs);margin-bottom:var(--space-3);color:#e65100;">
                      <i class="bi bi-info-circle"></i>
                      <strong>Amendment Reason:</strong> {{ $noteVer->amendment_reason }}
                      (Amended by: {{ $noteVer->amendedBy?->name }})
                    </div>
                  @endif

                  <div style="font-size:var(--font-size-xs);display:flex;flex-direction:column;gap:var(--space-1);color:var(--clr-grey-800);">
                    <div><strong>Diagnosis:</strong> {{ $noteVer->diagnosis }}</div>
                    @if($noteVer->treatment_plan)
                      <div><strong>Plan:</strong> {{ $noteVer->treatment_plan }}</div>
                    @endif
                    @if($noteVer->examination_findings)
                      <div><strong>Findings:</strong> {{ $noteVer->examination_findings }}</div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- AMENDMENT MODAL (AC-17) --}}
  @if($currentNote && $currentNote->is_finalized)
    <div x-show="amendModalOpen" x-cloak
         style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1050;display:flex;align-items:center;justify-content:center;padding:var(--space-4);">
      <div style="background:#fff;border-radius:var(--radius-lg);max-width:700px;width:100%;max-height:90vh;overflow-y:auto;padding:var(--space-6);box-shadow:var(--shadow-lg);"
           @click.away="amendModalOpen = false">
        <h3 style="margin:0 0 var(--space-2);color:var(--clr-green-dark);font-size:var(--font-size-lg);display:flex;align-items:center;gap:var(--space-2);">
          <i class="bi bi-pencil-square"></i> Create Clinical Note Amendment (Version {{ $currentNote->version + 1 }})
        </h3>
        <p style="font-size:var(--font-size-xs);color:var(--clr-grey-600);margin-bottom:var(--space-4);">
          Original version {{ $currentNote->version }} will be preserved in the audit log. The new amended note will become the active version.
        </p>

        <form method="POST" action="{{ route('consultations.amend', $encounter) }}">
          @csrf

          <div class="hmis-form-group" style="background:#fff8e1;padding:var(--space-3);border:1px solid #ffe082;border-radius:var(--radius-md);">
            <label class="hmis-label" for="amendment_reason" style="color:#e65100;">
              Mandatory Amendment Reason <span class="required">*</span>
            </label>
            <input type="text"
                   name="amendment_reason"
                   id="amendment_reason"
                   class="hmis-input"
                   placeholder="e.g. Added secondary diagnosis following lab report, amended dosage..."
                   required>
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label" for="amend_diagnosis">Diagnosis <span class="required">*</span></label>
            <input type="text"
                   name="diagnosis"
                   id="amend_diagnosis"
                   class="hmis-input"
                   value="{{ old('diagnosis', $currentNote->diagnosis) }}"
                   required>
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label" for="amend_treatment_plan">Treatment Plan</label>
            <textarea name="treatment_plan" id="amend_treatment_plan" class="hmis-input" rows="3">{{ old('treatment_plan', $currentNote->treatment_plan) }}</textarea>
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label" for="amend_examination_findings">Examination Findings</label>
            <textarea name="examination_findings" id="amend_examination_findings" class="hmis-input" rows="2">{{ old('examination_findings', $currentNote->examination_findings) }}</textarea>
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label" for="amend_follow_up">Follow-up Instructions</label>
            <input type="text" name="follow_up_instructions" id="amend_follow_up" class="hmis-input" value="{{ old('follow_up_instructions', $currentNote->follow_up_instructions) }}">
          </div>

          <div style="display:flex;justify-content:flex-end;gap:var(--space-3);margin-top:var(--space-5);">
            <button type="button" @click="amendModalOpen = false" class="btn-hmis-secondary">Cancel</button>
            <button type="submit" class="btn-hmis-primary">
              <i class="bi bi-check2-circle"></i> Save Version {{ $currentNote->version + 1 }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script>
function consultationWorkspace() {
  return {
    activeTab: 'notes',
    amendModalOpen: false,
    prescriptionItems: [
      { stock_item_id: '', drug_name: '', dose: '', frequency: 'TDS', duration: '5 days', quantity_prescribed: 15, instructions: '' }
    ],
    addMedicationItem() {
      this.prescriptionItems.push({
        stock_item_id: '',
        drug_name: '',
        dose: '',
        frequency: 'TDS',
        duration: '',
        quantity_prescribed: 1,
        instructions: ''
      });
    },
    removeMedicationItem(index) {
      if (this.prescriptionItems.length > 1) {
        this.prescriptionItems.splice(index, 1);
      }
    },
    onDrugSelect(index) {
      const select = event.target;
      const selectedOption = select.options[select.selectedIndex];
      if (selectedOption && selectedOption.dataset.name) {
        this.prescriptionItems[index].drug_name = selectedOption.dataset.name;
      }
    }
  }
}
</script>
@endpush
@endsection

