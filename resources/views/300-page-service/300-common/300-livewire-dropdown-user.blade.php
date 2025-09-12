{{-- 300-livewire-dropdown-user: 사용자 정보 드롭다운 컴포넌트 --}}
<div class="relative">
    {{-- 프로필 클릭 영역 --}}
    <div class="flex items-center gap-3 cursor-pointer"
         wire:click="toggleDropdown">
        <div class="text-right">
            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? '사용자' }}</div>
            <div class="text-xs text-gray-500">{{ Auth::user()->email ?? '' }}</div>
        </div>
        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center hover:bg-gray-400 transition-colors">
            <span class="text-sm font-medium text-gray-600">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
            </span>
        </div>
    </div>

    {{-- 드롭다운 메뉴 --}}
    <div x-data="{ open: @entangle('isOpen') }"
         x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="$wire.closeDropdown()"
         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
         style="display: none;">
        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            마이페이지
        </a>
        <div class="border-t border-gray-100 my-1"></div>
        <button wire:click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            로그아웃
        </button>
    </div>
</div>
