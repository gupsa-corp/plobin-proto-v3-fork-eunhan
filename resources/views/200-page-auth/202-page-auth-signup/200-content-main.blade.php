<main class="flex-1 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm mx-auto">
        <h2 class="text-2xl font-bold text-center mb-6">회원가입</h2>
        
        <livewire:auth.register />

        <div class="mt-6 text-center">
            <p class="text-gray-600">이미 계정이 있으신가요? 
                <a href="/login" class="text-blue-500 hover:text-blue-600">로그인</a>
            </p>
        </div>
    </div>
</main>
