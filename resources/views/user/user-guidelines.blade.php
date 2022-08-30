<div>
    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-guidelines.language', ['user' => $user])
    </div>

    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-guidelines.english', ['user' => $user])
    </div>

    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-guidelines.german', ['user' => $user])
    </div>

    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-guidelines.expert-mode', ['user' => $user])
    </div>

    <div class="max-w-7xl mx-auto py-10">
        @livewire('user-guidelines.inspirations', ['user' => $user])
    </div>
</div>