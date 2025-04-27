<div class="mb mobile__ponel"  id="step-mobile-2">
    <ul>
       
      
       
        <li>
            <button onclick="location.href='{{ url('/brifs') }}'" id="step-mobile-4" title="Просмотр и управление вашими брифами">
                <img src="/storage/icon/brif.svg" alt=""><span>Брифы</span>
            </button>
        </li>
        @if (Auth::user()->status == 'coordinator' ||
                Auth::user()->status == 'admin' ||
                Auth::user()->status == 'partner' ||
                Auth::user()->status == 'support' ||
                Auth::user()->status == 'architect' ||
                Auth::user()->status == 'designer' || Auth::user()->status == 'visualizer')
          <li>
            <button onclick="window.location.href='{{ route('ratings.specialists') }}'" title="Просмотр рейтингов специалистов">
                <img src="{{ asset('storage/icon/F-Chart.svg') }}" alt="Рейтинги специалистов">
                <span>Рейтинги</span>
            </button>
        </li>
        <li>
            <button onclick="location.href='{{ route('deal.cardinator') }}'" title="Просмотр и управление вашими сделками">
                <img src="/storage/icon/deal.svg" alt=""> <span>Сделка </span>
            </button>
        </li>
        {{-- <li>
            <button onclick="location.href='{{ url('/chats') }}'"  id="step-6">
                <img src="/storage/icon/chat.svg" alt=""> <span>Чат</span>
            </button>
        </li> --}}
    @else
        <li>
            <button onclick="location.href='{{ route('deal.user') }}'" id="step-mobile-5" title="Просмотр информации о вашей сделке">
                <img src="/storage/icon/deal.svg" alt=""><span>Сделка </span>
            </button>
        </li>
       
    @endif
        @if (Auth::user()->status == 'partner' || Auth::user()->status == 'admin')
            <li>
                <button onclick="location.href='{{ url('/estimate') }}'" title="Просмотр и управление сметами">
                    <img src="/storage/icon/estimates.svg" alt=""> <span>Сметы</span>
                </button>
            </li>
            
        @endif
  
        <li>
            <button onclick="location.href='{{ url('/profile') }}'" id="step-mobile-6" title="Просмотр и редактирование вашего профиля">
                <img src="/storage/icon/F-User.svg" alt=""><span>Профиль</span>
            </button>
        </li>
        @if (Auth::user()->status == 'admin' )
        <li>
            <button onclick="location.href='{{ url('/admin') }}'" title="Панель администрирования системы">
                <img src="/storage/icon/admin.svg" alt=""> <span>Админка</span>
            </button>
        </li>
    @endif
        {{-- <li>
            <button onclick="location.href='{{ url('/support') }}'"  id="step-mobile-7">
                <img src="/storage/icon/support.svg" alt=""><span>Помощь</span>
            </button>
        </li> --}}
    </ul>
</div>