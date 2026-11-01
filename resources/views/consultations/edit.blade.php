@extends('layouts.app')
@section('title', 'Consultation — ' . $encounter->encounter_number)
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li><a href="{{ route('consultations.index') }}" style="color:var(--clr-green);">My Queue</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>{{ $encounter->encounter_number }}</li>
@endsection

@push('styles')
<style>
.consult-layout { display: grid; grid-template-columns: 1fr 300px; gap: var(--sp-4); align-items: start; }
.consult-main { min-width: 0; }
.consult-sidebar { position: sticky; top: calc(var(--topbar-h) + var(--sp-4)); }
.tab-save-btn { display: none; }
.hmis-tab-panel.active .tab-save-btn { display: inline-flex; }
</style>
@endpush

@section('content')

{{-- Patient Header --}}
<div class="hmis-patient-header" style="margin-bottom:var(--sp-4);">
  <div class="hmis-patient-header__avatar"><i class="bi bi-person-fill"></i></div>
  <div style="flex:1;">
    <div class="hmis-patient-header__name">{{ $encounter->patient->full_name }}</div>
    <div class="hmis-patient-header__meta">
      <span><i class="bi bi-credit-card"></i> {{ $branchRecord?->mrn ?? '—' }}</span>
      @if($encounter->patient->date_of_birth)<span><i class="bi bi-calendar3"></i> {{ $encounter->patient->age }}</span>@endif
      @if($encounter->patient->gender)<span><i class="bi bi-gender-ambiguous"></i> {{ ucfirst($encounter->patient->gender) }}</span>@endif
      <span><i class="bi bi-telephone"></i> {{ $encounter->patient->phone_primary ?? '—' }}</span>
    </div>
  </div>
  @if($encounter->triageRecord)
    @php $t = $encounter->triageRecord; @endphp
    <div style="display:flex;gap:var(--sp-4);flex-wrap:wrap;">
      @foreach([
        ['bi-thermometer-half', $t->temperature_c ? $t->temperature_c.'°C' : null],
        ['bi-heart-pulse',      $t->bp],
        ['bi-activity',         $t->pulse_bpm ? $t->pulse_bpm.' bpm' : null],
        ['bi-lungs',            $t->spo2_percent ? $t->spo2_percent.'%' : null],
      ] as [$icon, $val])
        @if($val)
          <div style="text-align:center;">
            <i class="bi {{ $icon }}" style="color:rgba(255,255,255,.5);font-size:.8rem;display:block;"></i>
            <span style="color:#fff;font-size:var(--fs-xs);font-weight:var(--fw-semibold);">{{ $val }}</span>
          </div>
        @endif
      @endforeach
    </div>
  @endif
  <div>
    @php
      $sb = ['in_progress'=>'amber','consultation_complete'=>'green','in_queue'=>'blue'];
    @endphp
    <span class="hmis-badge hmis-badge--{{ $sb[$encounter->status] ?? 'grey' }}" style="padding:6px 14px;font-size:var(--fs-sm);">
      {{ ucfirst(str_replace('_',' ',$encounter->status)) }}
    </span>
  </div>
</div>

@if($note?->is_finalized)
  <div class="hmis-alert hmis-alert--success" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-lock-fill"></i>
    <div>Consultation notes finalized at {{ $note->finalized_at?->format('H:i, d M Y') }} by {{ $note->authoredBy?->name }}.</div>
  </div>
@endif

{{-- MAIN TABS --}}
<div class="consult-layout" x-data="{ tab: 'notes', subtab: 'complaints' }">
  <div class="consult-main">

    {{-- Tab bar --}}
    <div class="hmis-card" style="margin-bottom:var(--sp-4);overflow:hidden;">
      <div class="hmis-tabs" style="padding:0 var(--sp-2);background:var(--clr-grey-50);border-bottom:2px solid var(--border);">
        @foreach([
          ['notes',       'bi-journal-medical',   'Patient Notes'],
          ['procedures',  'bi-activity',           'Procedures'],
          ['labs',        'bi-flask',              'Lab Results'],
          ['prescription','bi-capsule',            'Prescription'],
          ['plan',        'bi-clipboard2-check',   'Management'],
          ['billing',     'bi-receipt',            'Billing'],
          ['referral',    'bi-arrow-right-circle', 'Referral'],
        ] as [$id,$icon,$label])
          <button class="hmis-tab" :class="{ 'active': tab === '{{ $id }}' }"
                  @click="tab = '{{ $id }}'" type="button">
            <i class="bi {{ $icon }}"></i> {{ $label }}
          </button>
        @endforeach
      </div>

      {{-- TAB: PATIENT NOTES --}}
      <div x-show="tab === 'notes'" x-transition>
        {{-- Subtab bar --}}
        <div class="hmis-subtabs">
          @foreach([
            ['chart',    'bi-person-vcard',    'Patient Chart'],
            ['complaints','bi-chat-text',      'Complaints'],
            ['signs',    'bi-clipboard2',      'Signs'],
            ['hpi',      'bi-file-text',       'History of Illness'],
            ['pmh',      'bi-clock-history',   'Past Medical'],
            ['fsh',      'bi-people',          'Family/Social'],
            ['duh',      'bi-capsule',         'Drug History'],
            ['exam',     'bi-stethoscope',     'Examination'],
            ['dx',       'bi-journal-check',   'Diagnosis'],
          ] as [$sid,$sicon,$slabel])
            <button class="hmis-subtab" :class="{ 'active': subtab === '{{ $sid }}' }"
                    @click="subtab = '{{ $sid }}'" type="button">
              <i class="bi {{ $sicon }}"></i> {{ $slabel }}
            </button>
          @endforeach
        </div>

        <form method="POST" action="{{ route('consultations.update', $encounter) }}">
          @csrf @method('PUT')
          <input type="hidden" name="section" value="notes">

          <div style="padding:var(--sp-5);">

            {{-- Patient Chart subtab --}}
            <div x-show="subtab === 'chart'" x-transition>
              <div class="hmis-form-section">Patient Overview</div>
              <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--sp-3);margin-bottom:var(--sp-5);">
                @foreach([
                  ['Full Name',    $encounter->patient->full_name],
                  ['Date of Birth',$encounter->patient->date_of_birth?->format('d M Y') ?? '—'],
                  ['Age',          $encounter->patient->age ?? '—'],
                  ['Gender',       ucfirst($encounter->patient->gender ?? '—')],
                  ['National ID',  $encounter->patient->national_id ?? '—'],
                  ['Phone',        $encounter->patient->phone_primary ?? '—'],
                  ['County',       $encounter->patient->county ?? '—'],
                  ['MRN',          $branchRecord?->mrn ?? '—'],
                ] as [$l,$v])
                  <div style="background:var(--clr-grey-50);border-radius:var(--r-md);padding:var(--sp-3);border:1px solid var(--border);">
                    <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);margin-bottom:2px;">{{ $l }}</div>
                    <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);">{{ $v }}</div>
                  </div>
                @endforeach
              </div>
              @if($encounter->patient->next_of_kin_name)
                <div class="hmis-alert hmis-alert--info">
                  <i class="bi bi-people-fill"></i>
                  <div>Next of kin: <strong>{{ $encounter->patient->next_of_kin_name }}</strong> ({{ $encounter->patient->next_of_kin_relation }}) — {{ $encounter->patient->next_of_kin_phone }}</div>
                </div>
              @endif
            </div>

            {{-- Complaints --}}
            <div x-show="subtab === 'complaints'" x-transition>
              <div class="hmis-form-section">Presenting Complaints</div>
              <div class="hmis-form-group">
                <label class="hmis-label">Chief Complaint / Presenting Complaints</label>
                <textarea name="presenting_complaints" class="hmis-input" rows="5"
                          placeholder="Describe the patient's main complaints in their own words..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('presenting_complaints', $note?->presenting_complaints) }}</textarea>
              </div>
            </div>

            {{-- Signs --}}
            <div x-show="subtab === 'signs'" x-transition>
              <div class="hmis-form-section">Signs & Observations</div>
              @if($encounter->triageRecord)
                @php $t = $encounter->triageRecord; @endphp
                <div class="hmis-vitals-grid" style="margin-bottom:var(--sp-5);">
                  @foreach([
                    ['bi-thermometer-half','Temperature',$t->temperature_c ? $t->temperature_c.'°C' : '—'],
                    ['bi-heart-pulse','Blood Pressure',$t->bp ?? '—'],
                    ['bi-activity','Pulse',$t->pulse_bpm ? $t->pulse_bpm.' bpm' : '—'],
                    ['bi-lungs','SpO₂',$t->spo2_percent ? $t->spo2_percent.'%' : '—'],
                    ['bi-speedometer','Weight',$t->weight_kg ? $t->weight_kg.' kg' : '—'],
                    ['bi-rulers','Height',$t->height_cm ? $t->height_cm.' cm' : '—'],
                    ['bi-calculator','BMI',$t->bmi ?? '—'],
                  ] as [$icon,$label,$value])
                    <div class="hmis-vital">
                      <i class="bi {{ $icon }}"></i>
                      <div class="hmis-vital__label">{{ $label }}</div>
                      <div class="hmis-vital__value">{{ $value }}</div>
                    </div>
                  @endforeach
                </div>
              @endif
              <div class="hmis-form-group">
                <label class="hmis-label">Additional Signs / Observations</label>
                <textarea name="signs" class="hmis-input" rows="4"
                          placeholder="Record clinical signs observed..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('signs', $note?->signs ?? '') }}</textarea>
              </div>
            </div>

            {{-- HPI --}}
            <div x-show="subtab === 'hpi'" x-transition>
              <div class="hmis-form-section">History of Present Illness</div>
              <div class="hmis-form-group">
                <textarea name="history_of_illness" class="hmis-input" rows="6"
                          placeholder="Onset, duration, character, associated symptoms, aggravating/relieving factors..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('history_of_illness', $note?->history_of_illness) }}</textarea>
              </div>
            </div>

            {{-- PMH --}}
            <div x-show="subtab === 'pmh'" x-transition>
              <div class="hmis-form-section">Past Medical & Surgical History</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
                <div class="hmis-form-group">
                  <label class="hmis-label">Past Medical History</label>
                  <textarea name="past_medical_history" class="hmis-input" rows="5"
                            placeholder="Chronic conditions, previous illnesses..."
                            {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('past_medical_history', $note?->past_medical_history) }}</textarea>
                </div>
                <div class="hmis-form-group">
                  <label class="hmis-label">Past Surgical History</label>
                  <textarea name="past_surgical_history" class="hmis-input" rows="5"
                            placeholder="Previous operations and procedures..."
                            {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('past_surgical_history', $note?->past_surgical_history) }}</textarea>
                </div>
              </div>
            </div>

            {{-- Family/Social History --}}
            <div x-show="subtab === 'fsh'" x-transition>
              <div class="hmis-form-section">Family & Social History</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
                <div class="hmis-form-group">
                  <label class="hmis-label">Family History</label>
                  <textarea name="family_history" class="hmis-input" rows="5"
                            placeholder="Hereditary conditions, family medical history..."
                            {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('family_history', $note?->family_history) }}</textarea>
                </div>
                <div class="hmis-form-group">
                  <label class="hmis-label">Social History</label>
                  <textarea name="social_history" class="hmis-input" rows="5"
                            placeholder="Occupation, lifestyle, habits, living situation..."
                            {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('social_history', $note?->social_history) }}</textarea>
                </div>
              </div>
            </div>

            {{-- Drug Use History --}}
            <div x-show="subtab === 'duh'" x-transition>
              <div class="hmis-form-section">Drug Use & Medication History</div>
              <div class="hmis-form-group">
                <textarea name="drug_history" class="hmis-input" rows="5"
                          placeholder="Current medications, dosages, allergies, substances..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('drug_history', $note?->drug_history) }}</textarea>
              </div>
            </div>

            {{-- Examination --}}
            <div x-show="subtab === 'exam'" x-transition>
              <div class="hmis-form-section">Physical Examination Findings</div>
              <div class="hmis-form-group">
                <textarea name="examination_findings" class="hmis-input" rows="7"
                          placeholder="General appearance, systemic examination findings..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('examination_findings', $note?->examination_findings) }}</textarea>
              </div>
            </div>

            {{-- Diagnosis --}}
            <div x-show="subtab === 'dx'" x-transition>
              <div class="hmis-form-section">Diagnosis</div>
              <div class="hmis-form-group">
                <label class="hmis-label">Clinical Diagnosis</label>
                <textarea name="diagnosis" class="hmis-input" rows="4"
                          placeholder="Primary and secondary diagnoses..."
                          {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('diagnosis', $note?->diagnosis) }}</textarea>
              </div>
              <div class="hmis-form-group">
                <label class="hmis-label">ICD-10 Codes</label>
                <input type="text" name="diagnosis_codes" class="hmis-input"
                       value="{{ old('diagnosis_codes', is_array($note?->diagnosis_codes) ? implode(', ', $note->diagnosis_codes) : $note?->diagnosis_codes) }}"
                       placeholder="e.g. J06.9, Z87.39"
                       {{ $note?->is_finalized ? 'disabled' : '' }}>
                <span class="hmis-hint">Separate multiple codes with commas.</span>
              </div>
            </div>

          </div>

          @if(!$note?->is_finalized)
            <div style="padding:0 var(--sp-5) var(--sp-5);display:flex;gap:var(--sp-3);">
              <button type="submit" class="btn-hmis-primary">
                <i class="bi bi-floppy"></i> Save Notes
              </button>
            </div>
          @endif
        </form>
      </div>

      {{-- TAB: PROCEDURES --}}
      <div x-show="tab === 'procedures'" x-transition style="padding:var(--sp-6);">
        <div class="hmis-empty">
          <i class="bi bi-activity"></i>
          <div class="hmis-empty__title">Procedures</div>
          <p>Procedure recording will be available in a future update.</p>
        </div>
      </div>

      {{-- TAB: LAB RESULTS --}}
      <div x-show="tab === 'labs'" x-transition style="padding:var(--sp-6);">
        <div class="hmis-empty">
          <i class="bi bi-flask"></i>
          <div class="hmis-empty__title">Laboratory Results</div>
          <p>Laboratory module integration coming soon.</p>
        </div>
      </div>

      {{-- TAB: PRESCRIPTION --}}
      <div x-show="tab === 'prescription'" x-transition style="padding:var(--sp-5);"
           x-data="{
             items: [],
             addItem(id, name, price) {
               const ex = this.items.find(i => i.id == id);
               if(ex) { ex.qty++; } else { this.items.push({id,name,price,qty:1,freq:'TDS',duration:'5 days',instructions:''}); }
             },
             removeItem(idx) { this.items.splice(idx,1); }
           }">
        <div class="hmis-form-section">Prescribe Medication</div>

        @if($note?->is_finalized)
          <div class="hmis-alert hmis-alert--success" style="margin-bottom:var(--sp-4);">
            <i class="bi bi-lock-fill"></i>
            <div>Consultation finalized. Prescription has been issued.</div>
          </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-5);">
          {{-- Drug search --}}
          <div>
            <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);margin-bottom:var(--sp-3);color:var(--clr-grey-700);">
              Available Stock <span style="font-weight:400;color:var(--clr-grey-400);">({{ $stockItems->count() }} items)</span>
            </div>
            <div style="max-height:400px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--r-md);">
              @forelse($stockItems as $si)
                @php $stock = $si->stockAtBranch($encounter->branch_id); @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-3) var(--sp-4);border-bottom:1px solid rgba(0,0,0,.04);font-size:var(--fs-sm);">
                  <div>
                    <div style="font-weight:var(--fw-semibold);">{{ $si->name }}</div>
                    <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);">
                      {{ $si->unit_of_measure }} &mdash;
                      <span style="color:{{ ($stock?->quantity_on_hand ?? 0) <= $si->reorder_level ? 'var(--clr-danger)' : 'var(--clr-green)' }};">
                        {{ $stock?->quantity_on_hand ?? 0 }} in stock
                      </span>
                    </div>
                  </div>
                  @if(!$note?->is_finalized)
                    <button type="button" @click="addItem({{ $si->id }}, '{{ addslashes($si->name) }}', {{ $si->unit_price }})"
                            class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--fs-xs);">
                      <i class="bi bi-plus"></i> Add
                    </button>
                  @endif
                </div>
              @empty
                <div class="hmis-empty" style="padding:var(--sp-5);">
                  <i class="bi bi-capsule"></i>
                  <div class="hmis-empty__title">No drugs in stock</div>
                  <p>Add drugs to inventory first.</p>
                </div>
              @endforelse
            </div>
          </div>

          {{-- Prescription items --}}
          <div>
            <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);margin-bottom:var(--sp-3);color:var(--clr-grey-700);">
              Prescription Items
            </div>
            <div x-show="items.length === 0" style="background:var(--clr-grey-50);border:2px dashed var(--border);border-radius:var(--r-md);padding:var(--sp-6);text-align:center;">
              <i class="bi bi-capsule" style="font-size:2rem;color:var(--clr-grey-300);display:block;margin-bottom:var(--sp-3);"></i>
              <div style="font-size:var(--fs-sm);color:var(--clr-grey-400);">Add drugs from the stock list</div>
            </div>
            <form method="POST" action="{{ route('prescription.store', $encounter) }}" x-show="items.length > 0">
              @csrf
              <template x-for="(item, i) in items" :key="i">
                <div class="hmis-pos-item">
                  <input type="hidden" :name="`items[${i}][stock_item_id]`" :value="item.id">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--sp-3);">
                    <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);" x-text="item.name"></div>
                    <button type="button" @click="removeItem(i)" class="btn-hmis-ghost" style="padding:2px 6px;color:var(--clr-danger);">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2);">
                    <div>
                      <label class="hmis-label">Qty</label>
                      <input type="number" :name="`items[${i}][quantity]`" x-model.number="item.qty" class="hmis-input" min="1">
                    </div>
                    <div>
                      <label class="hmis-label">Frequency</label>
                      <select :name="`items[${i}][frequency]`" x-model="item.freq" class="hmis-select">
                        <option>OD</option><option>BD</option><option>TDS</option>
                        <option>QID</option><option>PRN</option><option>STAT</option>
                        <option>Nocte</option>
                      </select>
                    </div>
                    <div>
                      <label class="hmis-label">Duration</label>
                      <input type="text" :name="`items[${i}][duration]`" x-model="item.duration" class="hmis-input" placeholder="5 days">
                    </div>
                    <div>
                      <label class="hmis-label">Route</label>
                      <select :name="`items[${i}][route]`" class="hmis-select">
                        <option>Oral</option><option>IV</option><option>IM</option>
                        <option>Topical</option><option>Inhaled</option>
                      </select>
                    </div>
                    <div style="grid-column:1/-1;">
                      <label class="hmis-label">Instructions</label>
                      <input type="text" :name="`items[${i}][instructions]`" x-model="item.instructions" class="hmis-input" placeholder="e.g. Take after meals">
                    </div>
                  </div>
                </div>
              </template>
              <button type="submit" class="btn-hmis-primary" style="width:100%;justify-content:center;">
                <i class="bi bi-file-medical"></i> Issue Prescription
              </button>
            </form>
          </div>
        </div>
      </div>

      {{-- TAB: MANAGEMENT PLAN --}}
      <div x-show="tab === 'plan'" x-transition style="padding:var(--sp-5);">
        <form method="POST" action="{{ route('consultations.update', $encounter) }}">
          @csrf @method('PUT')
          <input type="hidden" name="section" value="plan">
          <div class="hmis-form-section">Management Plan</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
            <div class="hmis-form-group">
              <label class="hmis-label">Treatment Plan</label>
              <textarea name="treatment_plan" class="hmis-input" rows="5"
                        placeholder="Treatment steps, interventions, management approach..."
                        {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('treatment_plan', $note?->treatment_plan) }}</textarea>
            </div>
            <div class="hmis-form-group">
              <label class="hmis-label">Follow-up Instructions</label>
              <textarea name="follow_up_instructions" class="hmis-input" rows="5"
                        placeholder="When to return, what to watch for, lifestyle changes..."
                        {{ $note?->is_finalized ? 'disabled' : '' }}>{{ old('follow_up_instructions', $note?->follow_up_instructions) }}</textarea>
            </div>
          </div>
          @if(!$note?->is_finalized)
            <div style="display:flex;gap:var(--sp-3);">
              <button type="submit" class="btn-hmis-secondary"><i class="bi bi-floppy"></i> Save Plan</button>
              <button type="submit" name="finalize" value="1" class="btn-hmis-primary"
                      onclick="return confirm('Finalize the consultation? This will lock the notes.')">
                <i class="bi bi-check-circle-fill"></i> Finalize & Proceed to Billing
              </button>
            </div>
          @endif
        </form>
      </div>

      {{-- TAB: BILLING --}}
      <div x-show="tab === 'billing'" x-transition style="padding:var(--sp-5);">
        @php $invoice = \App\Models\Billing\Invoice::where('encounter_id',$encounter->id)->first(); @endphp
        @if($invoice)
          <div class="hmis-alert hmis-alert--success" style="margin-bottom:var(--sp-4);">
            <i class="bi bi-receipt"></i>
            <div>
              Invoice <strong>{{ $invoice->invoice_number }}</strong> —
              Balance: <strong>KSh {{ number_format($invoice->balance_due, 2) }}</strong>
              <a href="{{ route('billing.show', $invoice) }}" class="btn-hmis-primary" style="margin-left:var(--sp-3);padding:4px 12px;font-size:var(--fs-xs);">
                <i class="bi bi-eye"></i> View Invoice
              </a>
            </div>
          </div>
        @else
          <div class="hmis-empty">
            <i class="bi bi-receipt"></i>
            <div class="hmis-empty__title">No invoice yet</div>
            @if($note?->is_finalized)
              <p>Consultation is complete. Create the invoice below.</p>
              <a href="{{ route('billing.create-encounter', $encounter) }}" class="btn-hmis-primary" style="margin-top:var(--sp-4);">
                <i class="bi bi-receipt"></i> Create Invoice
              </a>
            @else
              <p>Finalize the consultation first before billing.</p>
            @endif
          </div>
        @endif
      </div>

      {{-- TAB: REFERRAL --}}
      <div x-show="tab === 'referral'" x-transition style="padding:var(--sp-5);"
           x-data="{ showLetter: false, dest: '', reason: '', urgency: 'routine' }">
        <div class="hmis-form-section">Referral (Optional)</div>
        <div style="max-width:600px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);margin-bottom:var(--sp-4);">
            <div class="hmis-form-group">
              <label class="hmis-label">Referring to</label>
              <input type="text" x-model="dest" class="hmis-input" placeholder="e.g. Kenyatta National Hospital">
            </div>
            <div class="hmis-form-group">
              <label class="hmis-label">Urgency</label>
              <select x-model="urgency" class="hmis-select">
                <option value="routine">Routine</option>
                <option value="urgent">Urgent</option>
                <option value="emergency">Emergency</option>
              </select>
            </div>
          </div>
          <div class="hmis-form-group">
            <label class="hmis-label">Reason for Referral</label>
            <textarea x-model="reason" class="hmis-input" rows="3" placeholder="Clinical reason for referral..."></textarea>
          </div>
          <div style="display:flex;gap:var(--sp-3);">
            <button type="button" @click="showLetter = dest && reason" class="btn-hmis-primary">
              <i class="bi bi-file-text"></i> Generate Referral Letter
            </button>
          </div>

          {{-- Referral letter preview --}}
          <div x-show="showLetter" x-transition style="margin-top:var(--sp-5);">
            <div class="hmis-referral-letter" id="referral-letter">
              <div style="text-align:center;margin-bottom:var(--sp-4);">
                <div style="font-size:1.1rem;font-weight:700;">{{ auth()->user()->branch->name ?? 'Afya Core HMIS' }}</div>
                <div style="font-size:.9rem;color:#666;">{{ auth()->user()->branch?->address }}</div>
                <div style="font-size:.9rem;color:#666;">{{ auth()->user()->branch?->phone }}</div>
              </div>
              <hr style="margin:var(--sp-4) 0;">
              <div><strong>Date:</strong> {{ now()->format('d F Y') }}</div>
              <div><strong>To:</strong> <span x-text="dest"></span></div>
              <br>
              <p><strong>RE: REFERRAL LETTER</strong></p>
              <p>Dear Doctor,</p>
              <p>
                I am writing to refer my patient, <strong>{{ $encounter->patient->full_name }}</strong>
                ({{ $encounter->patient->gender ? ucfirst($encounter->patient->gender) : '' }},
                {{ $encounter->patient->age }}), for further evaluation and management.
              </p>
              <p><strong>Reason for Referral:</strong> <span x-text="reason"></span></p>
              @if($note?->diagnosis)
                <p><strong>Diagnosis:</strong> {{ $note->diagnosis }}</p>
              @endif
              @if($note?->treatment_plan)
                <p><strong>Treatment Given:</strong> {{ $note->treatment_plan }}</p>
              @endif
              <p>Please review and manage as appropriate. Your findings and recommendations would be appreciated.</p>
              <br>
              <p>Yours faithfully,</p>
              <br><br>
              <p><strong>{{ auth()->user()->name }}</strong><br>
              {{ config('hmis.roles.' . auth()->user()->role) }}<br>
              {{ auth()->user()->branch?->name }}</p>
            </div>
            <div style="margin-top:var(--sp-4);display:flex;gap:var(--sp-3);">
              <button type="button" onclick="window.print()" class="btn-hmis-primary">
                <i class="bi bi-printer"></i> Print Letter
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- RIGHT SIDEBAR --}}
  <div class="consult-sidebar">
    {{-- Encounter summary --}}
    <div class="hmis-card" style="margin-bottom:var(--sp-4);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title" style="font-size:var(--fs-sm);">Encounter Info</h2>
      </div>
      <div class="hmis-card__body" style="padding:var(--sp-4);">
        @foreach([
          ['Visit Date',  $encounter->visit_date->format('d M Y')],
          ['Encounter',   $encounter->encounter_number],
          ['Arrival',     $encounter->arrival_time->format('H:i')],
          ['Assigned By', $encounter->createdBy?->name ?? '—'],
        ] as [$l,$v])
          <div style="margin-bottom:var(--sp-3);">
            <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);text-transform:uppercase;letter-spacing:.06em;">{{ $l }}</div>
            <div style="font-size:var(--fs-sm);font-weight:var(--fw-medium);">{{ $v }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Status timeline --}}
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title" style="font-size:var(--fs-sm);">Timeline</h2>
      </div>
      <div class="hmis-card__body" style="padding:var(--sp-4);">
        @foreach($encounter->statusLog->sortBy('changed_at') as $log)
          <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-3);position:relative;">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--clr-green);margin-top:5px;flex-shrink:0;"></div>
            <div>
              <div style="font-size:var(--fs-xs);font-weight:var(--fw-semibold);color:var(--clr-charcoal);">
                {{ ucfirst(str_replace('_',' ',$log->to_status)) }}
              </div>
              <div style="font-size:0.625rem;color:var(--clr-grey-400);">
                {{ $log->changedBy?->name }} · {{ $log->changed_at->format('H:i') }}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

@endsection