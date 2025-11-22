<x-filament-panels::page.simple>
    <div
        x-data="{
        isForgotOpen: false,
        fpStep: 1,
        strength: 0,
        checkStrength(val) {
            let score = 0;
            if (val.length === 0) { this.strength = 0; return; }
            if (val.length < 8) score = 1;
            if (val.length >= 8) score = 2;
            if (val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val)) score = 3;
            this.strength = score;
        }
    }"
        class="min-h-screen flex bg-white overflow-hidden"
    >
        {{-- BAGIAN KIRI: FORM LOGIN + FORGOT PASSWORD SLIDE --}}
        <div class="w-full md:w-1/2 relative overflow-hidden flex flex-col justify-center">
            <div
                class="flex w-[200%] transition-transform duration-500 ease-in-out h-full"
                :class="isForgotOpen ? '-translate-x-1/2' : 'translate-x-0'"
            >

                {{-- HALAMAN LOGIN --}}
                <div class="w-1/2 flex items-center justify-center p-10">
                    <div class="w-full max-w-md space-y-8">

                        <div class="text-center md:text-left">
                            <h1 class="text-4xl font-extrabold text-blue-600 tracking-tight">Classmate.</h1>
                            <p class="mt-2 text-gray-500">Selamat datang kembali! Silakan masuk.</p>
                        </div>

                        @if(session('error'))
                            <div class="p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded text-sm">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="authenticate" class="space-y-6">
                            {{ $this->form }}

                            <div class="flex justify-between items-center">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" class="h-4 w-4 text-blue-600 rounded border-gray-300">
                                    Ingat saya
                                </label>

                                <button
                                    type="button"
                                    class="text-sm font-bold text-blue-600 hover:underline"
                                    @click="isForgotOpen = true"
                                >
                                    Lupa password?
                                </button>
                            </div>

                            <button
                                type="submit"
                                class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg"
                            >
                                Masuk Sekarang
                            </button>

                            <div class="text-center pt-3">
                                <a
                                    href="/admin"
                                    class="text-xs font-semibold text-gray-400 hover:text-blue-600 hover:underline"
                                >
                                    Masuk sebagai Dosen / Admin
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- HALAMAN FORGOT PASSWORD --}}
                <div class="w-1/2 flex items-center justify-center p-10 bg-gray-50/30">
                    <div class="w-full max-w-md space-y-6">

                        <button
                            @click="isForgotOpen = false; setTimeout(() => { fpStep = 1 }, 500)"
                            class="flex items-center text-sm text-gray-400 hover:text-gray-700"
                        >
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Kembali ke Login
                        </button>

                        <h2 class="text-3xl font-bold">Lupa Password?</h2>
                        <p class="text-gray-500 text-sm">Kami akan mengirimkan kode OTP ke WhatsApp Anda.</p>

                        {{-- STEP 1 --}}
                        <form x-show="fpStep === 1" class="space-y-6">
                            <div>
                                <label class="text-sm font-bold text-gray-700">NIM Mahasiswa</label>
                                <input type="text"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-blue-500"
                                       placeholder="Contoh: 2024001"
                                >
                            </div>
                            <button class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl">
                                Kirim OTP
                            </button>
                        </form>

                        {{-- STEP 2 RESET PASSWORD --}}
                        <form x-show="fpStep === 2" class="space-y-6">
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Kode OTP</label>
                                <input type="text" maxlength="6"
                                       class="w-full px-4 py-3 rounded-lg text-center font-bold tracking-widest border border-gray-300"
                                       placeholder="######"
                                >
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Password Baru</label>
                                <input
                                    type="password"
                                    class="w-full px-4 py-3 border rounded-lg"
                                    placeholder="Min 8 karakter"
                                    @input="checkStrength($event.target.value)"
                                >

                                {{-- Strength Meter --}}
                                <div class="mt-2 flex gap-1 h-1">
                                    <div class="flex-1 rounded" :class="strength >= 1 ? 'bg-red-500' : 'bg-gray-200'"></div>
                                    <div class="flex-1 rounded" :class="strength >= 2 ? 'bg-yellow-500' : 'bg-gray-200'"></div>
                                    <div class="flex-1 rounded" :class="strength >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                </div>
                            </div>

                            <button class="w-full py-3 bg-green-600 text-white font-bold rounded-xl">
                                Simpan Password
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>

        {{-- BAGIAN KANAN GAMBAR --}}
        <div class="hidden md:block w-1/2 bg-blue-600 relative overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1"
                class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-multiply"
            >
            <div class="relative z-10 flex flex-col items-center justify-center h-full text-white">
                <h2 class="text-4xl font-bold mb-4">Future Begins Here.</h2>
                <p class="text-blue-100 text-lg max-w-md">Platform terintegrasi Classmate.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
