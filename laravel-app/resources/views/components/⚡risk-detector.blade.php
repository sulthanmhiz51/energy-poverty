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
    <section>
        <div class="relative bg-[#3c8000] h-24 py-4 px-4 sm:px-12 lg:px-44" style="background-image: url('{{ asset('storage/img/header.webp') }}');">
            <div class="static h-full flex items-center">
                <h3 class="text-xl font-bold text-[#f6f7ed]">AI Energy Poverty Risk Detector</h3>
                <img src="{{ asset('storage/img/lamp.webp') }}" alt="Lamp" class="hidden md:block absolute -bottom-12 -left-10 w-[280px]">
            </div>
        </div>
        <div class="py-32 lg:py-44 2xl:py-60 text-center bg-cover bg-center bg-[#f6f7ed]" style="background-image: url('{{ asset('storage/img/sect1.webp') }}');">
            <div class="max-w-3xl mx-auto px-4">
                <div class="text-[#3c8000] mb-12">
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 drop-shadow-sm">
                        Not everyone has the same power
                    </h1>
                    <p class="font-medium text-lg md:text-xl text-gray-800 drop-shadow-sm">
                        With varying amounts of electrical uses and income, you can use this AI to detect which households fall into priority categories for energy subsidies.
                    </p>
                </div>
                <div class="flex flex-col items-center justify-center text-[#3c8000] hover:text-[#b2d235] transition-colors duration-300 cursor-pointer">
                    <h3 class="text-xl font-bold mb-2">Try it now!</h3>
                    <svg class="fill-current w-10 h-10 animate-bounce" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.02 319.26">
                        <path d="M5.9 48.96 48.97 5.89c7.86-7.86 20.73-7.84 28.56 0l178.48 178.48L434.5 5.89c7.86-7.86 20.74-7.82 28.56 0l43.07 43.07c7.83 7.84 7.83 20.72 0 28.56l-192.41 192.4-.36.37-43.07 43.07c-7.83 7.82-20.7 7.86-28.56 0l-43.07-43.07-.36-.37L5.9 77.52c-7.87-7.86-7.87-20.7 0-28.56z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#3c8000] bg-cover bg-center py-12 border-y border-gray-200 shadow-inner" style="background-image: url('{{ asset('storage/img/sect2.webp') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl shadow-2xl grid grid-cols-1 lg:grid-cols-12 bg-[#f6f7ed]/95 backdrop-blur-xl border border-white/20">
                
                <div class="p-6 md:p-8 lg:col-span-8 border-b md:border-b-0 md:border-r border-gray-300 h-auto">
                    <h2 class="text-2xl font-extrabold mb-6 text-[#3c8000] border-b border-[#3c8000]/20 pb-3">Household Profile</h2>
                    
                    <form wire:submit="analyze" class="space-y-8">
                        
                        <div>
                            <h3 class="text-sm font-bold text-[#b2d235] bg-[#3c8000] inline-block px-3 py-1 rounded-md uppercase tracking-wider mb-4 shadow-sm">Demographics</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Total Family Size</label>
                                    <input type="number" wire:model="family_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition" required min="1">
                                    @error('family_size') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Employed Members</label>
                                    <input type="number" wire:model="employed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Members Under 5 Years</label>
                                    <input type="number" wire:model="under_5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Members 5-17 Years</label>
                                    <input type="number" wire:model="age_5_17" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700">Head Education Rank</label>
                                    <select wire:model="education" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition bg-white">
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
                            <h3 class="text-sm font-bold text-[#b2d235] bg-[#3c8000] inline-block px-3 py-1 rounded-md uppercase tracking-wider mb-4 shadow-sm">Economics (USD)</h3>
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
                                    <label class="block text-xs font-bold text-gray-700">Monthly Income</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm font-bold">$</span></div>
                                        <input type="text" x-model="display" @input="formatInput" class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border py-2 transition" required>
                                    </div>
                                    @error('income') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
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
                                    <label class="block text-xs font-bold text-gray-700">Utility Expenditure</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm font-bold">$</span></div>
                                        <input type="text" x-model="display" @input="formatInput" class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border py-2 transition" required>
                                    </div>
                                    @error('utilities') <span class="text-xs text-red-600 font-bold mt-1 block">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-[#b2d235] bg-[#3c8000] inline-block px-3 py-1 rounded-md uppercase tracking-wider mb-4 shadow-sm">Housing & Infrastructure</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Floor Area (sqm)</label>
                                    <input type="number" wire:model="floor_area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Bedrooms</label>
                                    <input type="number" wire:model="bedrooms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">Electricity Access</label>
                                    <select wire:model="electricity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-3 py-2 transition bg-white">
                                        <option value="1">Yes (Grid)</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-[#b2d235] bg-[#3c8000] inline-block px-3 py-1 rounded-md uppercase tracking-wider mb-4 shadow-sm">Assets (Quantity)</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 text-center">Fridge</label>
                                    <input type="number" wire:model="fridge" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-2 py-2 text-center transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 text-center">AC</label>
                                    <input type="number" wire:model="ac" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-2 py-2 text-center transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 text-center">Washer</label>
                                    <input type="number" wire:model="washing_machine" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-2 py-2 text-center transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 text-center">TV</label>
                                    <input type="number" wire:model="tv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-2 py-2 text-center transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 text-center">PC</label>
                                    <input type="number" wire:model="pc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#3c8000] focus:ring focus:ring-[#b2d235]/50 sm:text-sm border px-2 py-2 text-center transition">
                                </div>
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="w-full bg-[#3c8000] text-[#f6f7ed] font-extrabold text-lg py-4 px-4 rounded-xl hover:bg-[#b2d235] hover:text-[#3c8000] shadow-lg disabled:opacity-50 transition-all duration-300">
                            <span wire:loading.remove>Run AI Analysis</span>
                            <span wire:loading>Processing Neural Data...</span>
                        </button>
                    </form>
                </div>

                <div class="bg-cover bg-center p-6 lg:col-span-4 flex flex-col items-center justify-start text-center relative" style="background-image: url('{{ asset('storage/img/paper_texture.webp') }}');">
                    <div class="absolute inset-0 bg-[#f6f7ed]/70 z-0"></div>
                    
                    <div class="relative z-10 w-full">
                        @if(!$result)
                            <div class="text-[#3c8000]/60 mt-32">
                                <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-lg font-bold tracking-wide">Awaiting Data...</p>
                            </div>
                        @elseif(isset($result['error']))
                            <div class="w-full bg-red-100 border-l-4 border-red-600 text-red-800 px-4 py-4 rounded shadow-sm text-left">
                                <strong class="font-bold block mb-1">API Connection Error</strong>
                                <span class="block text-sm">{{ $result['error'] }}</span>
                                <button wire:click="resetForm" class="mt-4 w-full bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700 transition">Try Again</button>
                            </div>
                        @else
                            <div wire:transition class="w-full space-y-4">
                                
                                <div class="p-6 rounded-xl border-2 shadow-sm
                                    @if($result['color'] === 'red') bg-red-50 border-red-300 text-red-800
                                    @elseif($result['color'] === 'yellow') bg-yellow-50 border-yellow-300 text-yellow-800
                                    @else bg-green-50 border-green-300 text-green-800 @endif">
                                    <h2 class="text-2xl font-black uppercase tracking-wider">{{ $result['status'] }}</h2>
                                    <p class="text-sm mt-2 font-bold opacity-80">Poverty Score: {{ $result['score'] }} / 100</p>
                                </div>

                                <div class="bg-white/90 border border-gray-200 rounded-lg p-5 text-sm text-gray-800 text-left shadow-sm">
                                    <strong class="text-[#3c8000] text-base block mb-2 border-b pb-1">Diagnostic Reasoning</strong>
                                    <p class="leading-relaxed font-medium">{{ $result['reasoning'] }}</p>
                                </div>

                                <div class="mt-4 pt-4 border-t border-[#3c8000]/20">
                                    <div class="flex items-center justify-between text-xs font-bold mb-3">
                                        <span class="text-[#3c8000] uppercase tracking-wider">Neural Diagnostics</span>
                                        
                                        @if($result['rf_validation'] === 'Consistent')
                                            <span class="inline-flex items-center text-green-700 bg-green-100 px-2 py-1 rounded shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                Models Aligned
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-yellow-700 bg-yellow-100 px-2 py-1 rounded shadow-sm" title="The Neural Model disagrees with the Rule-Based score.">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                Divergence Detected
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                                        <div class="bg-white/80 p-3 rounded-lg border border-gray-200 shadow-sm">
                                            <span class="block text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-bold">RF Prediction</span>
                                            <strong class="text-base 
                                                @if($result['rf_prediction'] === 'High') text-red-600
                                                @elseif($result['rf_prediction'] === 'Medium') text-yellow-600
                                                @else text-green-600 @endif
                                            ">{{ $result['rf_prediction'] }}</strong>
                                        </div>
                                        <div class="bg-white/80 p-3 rounded-lg border border-gray-200 shadow-sm">
                                            <span class="block text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-bold">Confidence</span>
                                            <strong class="text-base
                                                @if($result['rf_confidence'] >= 80) text-green-600
                                                @elseif($result['rf_confidence'] >= 50) text-yellow-600
                                                @else text-red-600 @endif
                                            ">{{ $result['rf_confidence'] }}%</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <button wire:click="resetForm" class="w-full mt-6 bg-[#3c8000] text-[#f6f7ed] font-bold py-3 px-4 rounded-lg hover:bg-[#2a5900] transition shadow-md">
                                    Analyze New Household
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative h-[80vh] bg-cover bg-center bg-fixed" style="background-image: url('{{ asset('storage/img/sect3.webp') }}');">
        <div class="absolute inset-0"></div>
        
        <div class="relative z-10 h-full flex flex-col items-center justify-center max-w-4xl mx-auto px-4 text-center text-[#3c8000]">
            <span class="text-[#b2d235] font-extrabold tracking-widest uppercase mb-4 text-sm">Beyond The Data</span>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                Systemic Sustainability in Policy Making
            </h2>
            <p class="text-lg md:text-xl font-medium mb-10 opacity-90 leading-relaxed">
                By bridging machine learning with robust legislative frameworks, this tool removes human bias from subsidy distribution. We ensure that energy assistance reaches the most vulnerable demographics based strictly on verifiable economic and infrastructure data.
            </p>
        </div>
    </section>
</div>