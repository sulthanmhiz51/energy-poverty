<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('AI Energy Poverty Risk Detector')] class extends Component
{
    // 1. The 15 Active Variables
    public $income = '';
    public $utilities = '';
    public $education = 2; // Default to High School/Undergrad
    public $family_size = '';
    public $under_5 = 0;
    public $age_5_17 = 0;
    public $employed = 1;
    public $floor_area = '';
    public $bedrooms = '';
    public $electricity = 1; // 1 = Yes, 0 = No
    
    // Assets
    public $fridge = 0;
    public $ac = 0;
    public $washing_machine = 0;
    public $tv = 0;
    public $pc = 0;
    
    public $result = null; 

    public function analyze()
    {
        // Basic safety validation to prevent API crashes
        $this->validate([
            'income' => 'required|numeric',
            'utilities' => 'required|numeric',
            'family_size' => 'required|numeric|min:1',
        ]);

        $payload = [
            'income' => (float) $this->income,
            'utilities' => (float) $this->utilities,
            'education' => (int) $this->education,
            'family_size' => (int) $this->family_size,
            'under_5' => (int) $this->under_5,
            'age_5_17' => (int) $this->age_5_17,
            'employed' => (int) $this->employed,
            'floor_area' => (float) ($this->floor_area ?: 0),
            'bedrooms' => (int) ($this->bedrooms ?: 0),
            'electricity' => (int) $this->electricity,
            'fridge' => (int) $this->fridge,
            'ac' => (int) $this->ac,
            'washing_machine' => (int) $this->washing_machine,
            'tv' => (int) $this->tv,
            'pc' => (int) $this->pc,
        ];

        try {
            $response = Http::timeout(10)->post('http://127.0.0.1:8001/predict', $payload);
            
            if ($response->successful()) {
                $this->result = $response->json();
            } else {
                // THE FIX: This will now print the actual Python error on the UI
                $this->result = ['error' => 'API Error: ' . $response->body()];
            }
        } catch (\Exception $e) {
            $this->result = ['error' => 'Connection to AI Server refused.'];
        }
    }

    public function resetForm()
    {
        $this->reset();
    }
};
?>

<div>
    <section class="py-8 text-center bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-2">
                AI Energy Poverty Risk Detector
            </h1>
            <p class="text-gray-600">Streamlined 15-Parameter Evaluation Model</p>
        </div>
    </section>

    <section class="bg-gray-100 py-8 border-y border-gray-200 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                <div class="p-6 md:p-8 lg:col-span-8 border-b md:border-b-0 md:border-r border-gray-100 h-[800px] overflow-y-auto">
                    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-2">Household Profile</h2>
                    
                    <form wire:submit="analyze" class="space-y-8">
                        
                        <div>
                            <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">Demographics</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Total Family Size</label>
                                    <input type="number" wire:model="family_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2" required min="1">
                                    @error('family_size') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Employed Members</label>
                                    <input type="number" wire:model="employed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Members Under 5 Years</label>
                                    <input type="number" wire:model="under_5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Members 5-17 Years</label>
                                    <input type="number" wire:model="age_5_17" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700">Head Education Rank</label>
                                    <select wire:model="education" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                        <option value="0">0 - No Grade</option>
                                        <option value="1">1 - Primary/Elementary</option>
                                        <option value="2">2 - Secondary/Undergrad</option>
                                        <option value="3">3 - Degree Graduate</option>
                                        <option value="4">4 - Post-Graduate</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">Economics (USD)</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                
                                <div x-data="{
                                    display: '',
                                    init() { this.$watch('$wire.income', val => { if(!val) this.display = ''; }); },
                                    formatInput(e) {
                                        let raw = e.target.value.replace(/\D/g, '');
                                        $wire.income = raw ? parseInt(raw) : '';
                                        this.display = raw ? new Intl.NumberFormat('en-US').format(raw) : '';
                                    }
                                }">
                                    <label class="block text-xs font-medium text-gray-700">Monthly Income</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">$</span></div>
                                        <input type="text" x-model="display" @input="formatInput" class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border py-2" required>
                                    </div>
                                    @error('income') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div x-data="{
                                    display: '',
                                    init() { this.$watch('$wire.utilities', val => { if(!val) this.display = ''; }); },
                                    formatInput(e) {
                                        let raw = e.target.value.replace(/\D/g, '');
                                        $wire.utilities = raw ? parseInt(raw) : '';
                                        this.display = raw ? new Intl.NumberFormat('en-US').format(raw) : '';
                                    }
                                }">
                                    <label class="block text-xs font-medium text-gray-700">Utility Expenditure</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">$</span></div>
                                        <input type="text" x-model="display" @input="formatInput" class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border py-2" required>
                                    </div>
                                    @error('utilities') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">Housing & Infrastructure</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Floor Area (sqm)</label>
                                    <input type="number" wire:model="floor_area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Bedrooms</label>
                                    <input type="number" wire:model="bedrooms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Electricity Access</label>
                                    <select wire:model="electricity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-3 py-2">
                                        <option value="1">Yes (Grid Access)</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-3">Assets (Quantity)</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Fridge</label>
                                    <input type="number" wire:model="fridge" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-2 py-1 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">AC</label>
                                    <input type="number" wire:model="ac" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-2 py-1 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Washer</label>
                                    <input type="number" wire:model="washing_machine" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-2 py-1 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">TV</label>
                                    <input type="number" wire:model="tv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-2 py-1 text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">PC</label>
                                    <input type="number" wire:model="pc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm border px-2 py-1 text-center">
                                </div>
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 shadow-md disabled:opacity-50 transition">
                            <span wire:loading.remove>Run AI Analysis</span>
                            <span wire:loading>Processing Model...</span>
                        </button>
                    </form>
                </div>

                <div class="bg-gray-50 p-6 lg:col-span-4 flex flex-col items-center justify-start text-center">
                    
                    @if(!$result)
                        <div class="text-gray-400 mt-20">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-sm font-medium">Awaiting Household Data</p>
                        </div>
                    @elseif(isset($result['error']))
                        <div class="w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ $result['error'] }}</span>
                            <button wire:click="resetForm" class="mt-4 w-full bg-red-200 text-red-800 font-bold py-2 px-4 rounded hover:bg-red-300 transition">Try Again</button>
                        </div>
                    @else
                        <div wire:transition class="w-full space-y-4">
                            <div class="p-6 rounded-xl border-2 shadow-sm
                                @if($result['color'] === 'red') bg-red-50 border-red-200 text-red-800
                                @elseif($result['color'] === 'yellow') bg-yellow-50 border-yellow-200 text-yellow-800
                                @else bg-green-50 border-green-200 text-green-800 @endif">
                                <h2 class="text-xl font-black uppercase tracking-wide">{{ $result['status'] }}</h2>
                                <p class="text-sm mt-2 font-medium opacity-80">Poverty Score: {{ $result['score'] }} / 100</p>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-lg p-5 text-sm text-gray-700 text-left shadow-sm">
                                <strong class="text-gray-900 text-base block mb-2">Diagnostic Reasoning</strong>
                                <p class="leading-relaxed">{{ $result['reasoning'] }}</p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between text-xs font-medium">
                                    <span class="text-gray-500 uppercase tracking-wider">Model Diagnostics</span>
                                    
                                    @if($result['rf_validation'] === 'Consistent')
                                        <span class="inline-flex items-center text-green-600 bg-green-50 px-2 py-1 rounded">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            Models Aligned
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            Divergence Detected
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-600">
                                    <div class="bg-gray-50 p-2 rounded border border-gray-100">
                                        <span class="block text-xs text-gray-400 mb-1">Random Forest Prediction</span>
                                        <strong>{{ $result['rf_prediction'] }} Priority</strong>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded border border-gray-100">
                                        <span class="block text-xs text-gray-400 mb-1">Confidence Score</span>
                                        <strong>{{ $result['rf_confidence'] }}%</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <button wire:click="resetForm" class="w-full bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded hover:bg-gray-300 transition shadow-sm">
                                Analyze New Household
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</div>