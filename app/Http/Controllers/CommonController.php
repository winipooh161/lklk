<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Common;
use App\Models\Deal;
use Illuminate\Support\Facades\Http;

class CommonController extends Controller
{
    /**
     * CommonController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отображение формы с вопросами "Общего" брифа.
     *
     * @param  int  $id    ID конкретного брифа
     * @param  int  $page  Номер страницы (шаг) с вопросами
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function questions($id, $page)
    {
        // Пытаемся найти бриф по ID и по текущему пользователю
        $brif = Common::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$brif) {
            return redirect()->route('brifs.index')
                ->with('error', 'Бриф не найден или не принадлежит данному пользователю.');
        }

        // Если страница равна 0 (выбор комнат)
        if ($page == 0) {
            $rooms = [
                ['key' => 'room_prihod',       'title' => 'Прихожая', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_detskaya',      'title' => 'Детская', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_kladovaya',      'title' => 'Кладовая', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_kukhni_i_gostinaya','title' => 'Кухня и гостиная', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_gostevoi_sanuzel','title' => 'Гостевой санузел', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_gostinaya',      'title' => 'Гостиная', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_rabocee_mesto',  'title' => 'Рабочее место', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_stolovaya',      'title' => 'Столовая', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_vannaya',        'title' => 'Ванная комната', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_kukhnya',        'title' => 'Кухня', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_kabinet',        'title' => 'Кабинет', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_spalnya',        'title' => 'Спальня', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_garderobnaya',   'title' => 'Гардеробная', 'format' => 'room', 'type' => 'checkbox'],
                ['key' => 'room_druge',          'title' => 'Другое', 'format' => 'room', 'type' => 'checkbox'],
            ];
            
            $totalPages = 5; // Теперь у нас 5 страниц + страница выбора комнат
            return view('common.questions', [
                'questions'   => $rooms,
                'page'        => 0,
                'user'        => Auth::user(),
                'totalPages'  => $totalPages,
                'brif'        => $brif,
                'title'       => 'Выберите помещения',
                'subtitle'    => 'Отметьте те помещения, над которыми будем работать в проекте',
                'title_site'  => "Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн"
            ]);
        }

        // Новая структура вопросов для 5 страниц вместо 15
        $questions = [
            1 => [
                ['key' => 'question_1_1', 'title' => 'Сколько человек будет проживать в квартире?', 'subtitle' => 'Укажите количество жильцов, их пол и возраст для понимания потребностей каждого члена семьи', 'type' => 'textarea', 'placeholder' => 'Пример: семейная пара с ребенком (0 лет), в будущем планируем еще детей', 'format' => 'default'],
                ['key' => 'question_1_2', 'title' => 'Есть ли у вас домашние животные и комнатные растения?', 'subtitle' => 'Укажите вид и количество животных или растений, чтобы мы могли учесть их потребности при проектировании пространства.', 'type' => 'textarea', 'placeholder' => 'Пример: среднеразмерная собака бигль. Хотелось бы, чтобы напольное покрытие приглушало стук когтей, требуется место под лежанку и лапомойку на входе, место для еды. Растения в доме нужны, частично перевезем из старой квартиры, но хотелось бы еще добавить', 'format' => 'default'],
                ['key' => 'question_1_3', 'title' => 'Есть ли у членов семьи особые увлечения или хобби?', 'subtitle' => 'Укажите ( любимое занятие ,которое подразумевает в квартире особое место, к примеру полочки для хранения или выставки коллекции, место для швейной машинки и место для хранения принадлежностей для шитья). Это поможет нам создать функциональное пространство, соответствующее интересам и потребностям ваших близких', 'type' => 'textarea', 'placeholder' => 'Пример: Нужны зоны для хобби: место под электрогитару, усилитель и синтезатор, большой книжный шкаф', 'format' => 'default'],
                ['key' => 'question_1_4', 'title' => 'Требуется ли перепланировка? Каков состав помещений?', 'subtitle' => 'Опишите, какие изменения в планировке вы хотите осуществить.', 'type' => 'textarea', 'placeholder' => 'Пример: совместить кухню с гостиной. Спальня с гардеробной, детская, кабинет, кладовая, санузел', 'format' => 'default'],
                ['key' => 'question_1_5', 'title' => 'Как часто вы встречаете гостей?', 'subtitle' => 'Укажите, требуется ли предусмотреть дополнительные посадочные и спальные места и как часто вы ожидаете гостей', 'type' => 'textarea', 'placeholder' => 'Пример: Раз в месяц-два, на пару дней ', 'format' => 'default'],
                ['key' => 'question_1_6', 'title' => 'Адрес', 'subtitle' => 'Укажите адрес объекта (город, улица и дом, если есть - название ЖК)', 'type' => 'textarea', 'placeholder' => 'Пример: г. Грозный, ул. Лорсанова 15', 'format' => 'default'],
            ],
            2 => [
                ['key' => 'question_2_1', 'title' => 'Какой стиль Вы хотите видеть в своем интерьере? Какие цвета должны преобладать в интерьере?', 'subtitle' => 'Укажите предпочтения по стилям (например, современный, классический, минимализм) и цветам, которые вы хотите использовать в интерьере.', 'type' => 'textarea', 'placeholder' => 'Укажите предпочтения по стилям (например, современный, классический, минимализм) и цветам, которые вы хотите использовать в интерьере.', 'format' => 'default'],
             
                ['key' => 'question_2_2', 'title' => 'Какие имеющиеся предметы обстановки нужно включить в новый интерьер?', 'subtitle' => 'Перечислите мебель и аксессуары, которые вы хотите сохранить', 'type' => 'textarea', 'placeholder' => 'Перечислите мебель и аксессуары, которые вы хотите сохранить', 'format' => 'default'],
                ['key' => 'question_2_3', 'title' => 'В каком ценовом сегменте предполагается ремонт?', 'subtitle' => 'Укажите выбранный ценовой сегмент: эконом, средний+, бизнес или премиум', 'type' => 'textarea', 'placeholder' => 'Укажите выбранный ценовой сегмент: эконом, средний+, бизнес или премиум', 'format' => 'default'],
                ['key' => 'question_2_4', 'title' => 'Что не должно быть в вашем интерьере?', 'subtitle' => 'Перечислите элементы или материалы, которые вы не хотите видеть', 'type' => 'textarea', 'placeholder' => 'Перечислите элементы или материалы, которые вы не хотите видеть', 'format' => 'default'],
                ['key' => 'question_2_5', 'title' => 'Бюджет проекта', 'subtitle' => 'Укажите ориентировочную сумму бюджета, которую вы готовы потратить на ремонт, включая стоимость материалов', 'type' => 'textarea', 'placeholder' => 'Укажите ориентировочную сумму бюджета, которую вы готовы потратить на ремонт, включая стоимость материалов', 'format' => 'default'],
            ],
            3 => [ // Комнаты будут отфильтрованы позже на основе выбора на странице 0
                ['key' => 'question_3_1', 'title' => 'Прихожая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в прихожей? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_2', 'title' => 'Детская', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в детской? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_3', 'title' => 'Кладовая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кладовой? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_4', 'title' => 'Кухня и гостиная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кухне и гостиной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_5', 'title' => 'Гостевой санузел', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Перечислите предпочтения по выбору душа, раковины с тумбой, унитаза и других элементов.', 'format' => 'faq'],
                ['key' => 'question_3_6', 'title' => 'Гостиная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гостиной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_7', 'title' => 'Рабочее место', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в рабочей зоне? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_8', 'title' => 'Столовая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в столовой? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_9', 'title' => 'Ванная комната', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Укажите предпочтения по выбору ванны/душа, раковины с тумбой, унитаза, полотенцесушителя и стиральной машины.', 'format' => 'faq'],
                ['key' => 'question_3_10', 'title' => 'Кухня', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Укажите тип плиты, наличие посудомоечной машины, микроволновой печи, духового шкафа, мойки, холодильника и других приборов. Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_11', 'title' => 'Кабинет', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кабинете? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_12', 'title' => 'Спальня', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в спальне? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_13', 'title' => 'Гардеробная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гардеробной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_14', 'title' => 'Другое', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какие пожелания у вас есть по наполнению в других помещениях? Опишите детали и расстановку мебели.', 'format' => 'faq'],
            ],
            4 => [
                ['key' => 'question_4_1', 'title' => 'Напольные покрытия', 'subtitle' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'type' => 'textarea', 'placeholder' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'format' => 'default'],
                ['key' => 'question_4_2', 'title' => 'Двери', 'subtitle' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т п', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т.п.', 'format' => 'default'],
                ['key' => 'question_4_3', 'title' => 'Отделка стен', 'subtitle' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'format' => 'default'],
                ['key' => 'question_4_4', 'title' => 'Освещение и электрика', 'subtitle' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'type' => 'textarea', 'placeholder' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'format' => 'default'],
                ['key' => 'question_4_5', 'title' => 'Потолки', 'subtitle' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'format' => 'default'],
                ['key' => 'question_4_6', 'title' => 'Дополнительные пожелания', 'subtitle' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'type' => 'textarea', 'placeholder' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'format' => 'default'],
            ],
            5 => [
                ['key' => 'question_5_1', 'title' => 'Пожелания по звукоизоляции', 'subtitle' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'type' => 'textarea', 'placeholder' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'format' => 'default'],
                ['key' => 'question_5_2', 'title' => 'Теплые полы', 'subtitle' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены ', 'type' => 'textarea', 'placeholder' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены', 'format' => 'default'],
                ['key' => 'question_5_3', 'title' => 'Предпочтения по размещению и типу радиаторов', 'subtitle' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'format' => 'default'],
                ['key' => 'question_5_4', 'title' => 'Водоснабжение', 'subtitle' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'format' => 'default'],
                ['key' => 'question_5_5', 'title' => 'Кондиционирование и вентиляция', 'subtitle' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'type' => 'textarea', 'placeholder' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'format' => 'default'],
                ['key' => 'question_5_6', 'title' => 'Сети', 'subtitle' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии.', 'type' => 'textarea', 'placeholder' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии', 'format' => 'default'],
            ],
        ];

        // Если страница не нулевая, применяем фильтрацию по выбранным комнатам
        if ($page == 3) { // Только для страницы с комнатами (страница 3)
            // Получаем список выбранных комнат из JSON
            $selectedRooms = json_decode($brif->rooms, true) ?? [];
            
            if (!empty($selectedRooms)) {
                // Получаем названия комнат (значения из selectedRooms)
                $roomTitles = array_values($selectedRooms);
                
                // Фильтруем вопросы на странице 3, оставляя только те, что относятся к выбранным комнатам
                $questions[3] = array_filter($questions[3], function($question) use ($roomTitles) {
                    // Если формат вопроса faq и заголовок совпадает с названием комнаты
                    if ($question['format'] == 'faq') {
                        foreach ($roomTitles as $roomTitle) {
                            // Если заголовок вопроса совпадает с названием комнаты или заголовок "Другое"
                            if ($question['title'] == $roomTitle || $question['title'] == 'Другое') {
                                return true;
                            }
                        }
                        return false; // Если комната не выбрана, не показываем вопрос
                    }
                    return true; // Другие форматы вопросов показываем всегда
                });
            }
        }

        // Общие заголовки для страниц
        $titles = [
            1 => [
                'title'    => 'Общая информация',
                'subtitle' => 'Пожалуйста, предоставьте следующую информацию'
            ],
            2 => [
                'title'    => 'Интерьер: стиль и предпочтения',
                'subtitle' => 'Определитесь с общим стилем и цветовыми решениями'
            ],
            3 => [
                'title'    => 'Пожелания по помещениям',
                'subtitle' => 'Опишите пожелания по наполнению, деталям и расстановке по каждому помещению'
            ],
            4 => [
                'title'    => 'Пожелания по отделке помещений',
                'subtitle' => 'Опишите предпочтения по отделке помещений'
            ],
            5 => [
                'title'    => 'Пожелания по оснащению помещений',
                'subtitle' => 'Укажите, какие устройства или системы вы хотите установить, чтобы обеспечить комфорт и функциональность'
            ]
        ];
    
        // Если указанная страница не существует
        if (!isset($questions[$page])) {
            return redirect()->route('brifs.index')
                ->with('error', 'Неверный номер страницы вопросов.');
        }
    
        $title_site = "Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
        $totalPages = count($questions);
        return view('common.questions', [
            'questions' => $questions[$page],
            'page'      => $page,
            'user'      => $user,
            'totalPages'=> $totalPages,
            'brif'      => $brif,
            'title'     => $titles[$page]['title'] ?? '',
            'subtitle'  => $titles[$page]['subtitle'] ?? '',
            'title_site'=> $title_site
        ]);
    }

    /**
     * Сохранение ответов для указанного брифа на конкретной странице.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id    ID конкретного брифа
     * @param  int  $page  Текущая страница (шаг)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveAnswers(Request $request, $id, $page)
    {
        // Валидация входящих данных
        $data = $request->validate([
            'answers'      => 'nullable|array',
            'price'        => 'nullable|numeric',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|max:25600|mimes:pdf,xlsx,xls,doc,docx,jpg,jpeg,png,heic,heif',
            'skip_page'    => 'nullable|boolean'
        ]);
    
        // Находим бриф по ID и по текущему пользователю
        $brif = Common::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
    
        if (!$brif) {
            return redirect()->route('brifs.index')
                ->with('error', 'Бриф не найден или не принадлежит данному пользователю.');
        }
    
        // Обработка страницы выбора комнат (page 0)
        if ($page == 0) {
            $selectedRooms = $request->input('answers', []);
            $brif->rooms = json_encode($selectedRooms);
            $brif->current_page = 1;
            $brif->save();
            return redirect()->route('common.questions', ['id' => $brif->id, 'page' => 1]);
        }
    
        // Если передано поле price — обновляем его
        if (isset($data['price'])) {
            $brif->price = $data['price'];
        }
    
        // Определяем, пропущена ли страница, с учётом кнопки "skip"
        $isSkipped = $request->input('action') === 'skip'
            ? true
            : (bool)$request->input('skip_page', 0);
        
        // Если страница не пропущена, сохраняем ответы
        if (!$isSkipped) {
            // Обновляем ответы в соответствующих колонках таблицы
            if (isset($data['answers'])) {
                foreach ($data['answers'] as $key => $answer) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('commons', $key)) {
                        $brif->$key = $answer;
                    }
                }
            }
            
            // Обновляем массив пропущенных страниц - убираем текущую страницу, если она была пропущена ранее
            $skippedPages = json_decode($brif->skipped_pages ?? '[]', true);
            if (($key = array_search($page, $skippedPages)) !== false) {
                unset($skippedPages[$key]);
                $brif->skipped_pages = json_encode(array_values($skippedPages));
            }
        } else {
            // Если страница пропущена, добавляем ее в массив пропущенных (только если страница < 5)
            if ($page < 5) {
                $skippedPages = json_decode($brif->skipped_pages ?? '[]', true);
                if (!in_array($page, $skippedPages)) {
                    $skippedPages[] = $page;
                    $brif->skipped_pages = json_encode($skippedPages);
                }
            }
        }
        
        // Если это страница 2 — обработка загрузки файлов (референсы)
        if ($page == 2 && $request->hasFile('references')) {
            $uploadedFiles = [];
            $totalSize = 0;
            $userId = auth()->id();
    
            foreach ($request->file('references') as $file) {
                if ($file->isValid()) {
                    $fileSize = $file->getSize();
                    $totalSize += $fileSize;
    
                    if ($totalSize > 25 * 1024 * 1024) {
                        return redirect()->back()->with('error', 'Суммарный размер файлов не должен превышать 25 МБ.');
                    }
    
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $briefId = $brif->id;
                    $directory = public_path("uploads/references/user/{$userId}/commons/{$briefId}");
    
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
    
                    $file->move($directory, $filename);
                    $uploadedFiles[] = "uploads/references/user/{$userId}/commons/{$briefId}/{$filename}";
                } else {
                    return redirect()->back()->with('error', 'Один или несколько файлов имеют неверный формат.');
                }
            }
    
            if (!empty($uploadedFiles)) {
                $existingReferences = $brif->references ? json_decode($brif->references, true) : [];
                $brif->references = json_encode(array_merge($existingReferences, $uploadedFiles));
                $brif->save();
            }
        }
        
        // Если это страница 5 — обработка загрузки файлов
        if ($page == 5 && $request->hasFile('documents')) {
            $uploadedFiles = [];
            $totalSize = 0;
            $userId = auth()->id();
    
            foreach ($request->file('documents') as $file) {
                if ($file->isValid()) {
                    $fileSize = $file->getSize();
                    $totalSize += $fileSize;
    
                    if ($totalSize > 25 * 1024 * 1024) {
                        return redirect()->back()->with('error', 'Суммарный размер файлов не должен превышать 25 МБ.');
                    }
    
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $briefId = $brif->id;
                    $directory = public_path("uploads/documents/user/{$userId}/commons/{$briefId}");
    
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
    
                    $file->move($directory, $filename);
                    $uploadedFiles[] = "uploads/documents/user/{$userId}/commons/{$briefId}/{$filename}";
                } else {
                    return redirect()->back()->with('error', 'Один или несколько файлов имеют неверный формат.');
                }
            }
    
            if (!empty($uploadedFiles)) {
                $existingDocuments = $brif->documents ? json_decode($brif->documents, true) : [];
                $brif->documents = json_encode(array_merge($existingDocuments, $uploadedFiles));
            }
        }
    
        // Обработка действия «назад»
        if ($request->input('action') === 'prev') {
            $prevPage = $page > 1 ? $page - 1 : 1;
            $brif->current_page = $prevPage;
            $brif->save();
            return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $prevPage]);
        }
        
        // Если нажата кнопка "Пропустить", перенаправляем на следующую страницу
        if ($request->input('action') === 'skip') {
            $maxPage = 5; // Максимальная страница теперь 5 вместо 15
            if ($page < $maxPage) {
                $nextPage = $page + 1;
                $brif->current_page = $nextPage;
                $brif->save();
                return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $nextPage]);
            }
        }
        
        // Сохраняем текущую страницу в брифе
        $brif->current_page = $page;
        $brif->save();
        
        // Получаем список пропущенных страниц
        $skippedPages = json_decode($brif->skipped_pages ?? '[]', true);
        
        // Проверяем, была ли текущая страница в списке пропущенных
        $wasInSkippedList = in_array($page, $skippedPages);
        
        // Если текущая страница была пропущена ранее, удаляем её из списка
        if ($wasInSkippedList) {
            $skippedPages = array_diff($skippedPages, [$page]);
            $brif->skipped_pages = json_encode(array_values($skippedPages));
            $brif->save();
        }
        
        // Если список пропущенных страниц пуст (то есть все страницы заполнены)
        if (empty($skippedPages)) {
            // Если это была пропущенная страница ИЛИ страница 5, завершаем бриф
            if ($wasInSkippedList || $page == 5) {
                // Завершаем бриф
                $brif->status = 'Завершенный';
                $brif->save();
                
                // Связываем с сделкой, если она существует
                $deal = Deal::where('user_id', auth()->id())->first();
                if ($deal) {
                    $brif->deal_id = $deal->id;
                    $brif->save();
                    
                    $deal->common_id = $brif->id;
                    $deal->update([
                        'client_name'   => auth()->user()->name,
                        'client_phone'  => auth()->user()->phone ?? 'N/A',
                        'total_sum'     => $brif->price ?? 0,
                        'status'        => 'Бриф прикриплен',
                        'link'          => "/common/{$brif->id}",
                    ]);
                }
                
                return redirect()->route('deal.user')
                    ->with('success', 'Бриф успешно заполнен!');
            }
        }
        
        // Если остались ещё пропущенные страницы, переходим к следующей пропущенной
        if (!empty($skippedPages)) {
            sort($skippedPages); // Сортируем по возрастанию
            $nextPage = $skippedPages[0];
            return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $nextPage])
                ->with('warning', 'У вас остались пропущенные вопросы. Пожалуйста, заполните их.');
        }
        
        // Если это обычная страница (не пропущенная и не страница 5)
        if ($page < 5) {
            // Просто переходим к следующей странице
            $nextPage = $page + 1;
            return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $nextPage]);
        } else if ($page == 5) {
            // Если страница 5, проверяем наличие пропущенных страниц
            if (!empty($skippedPages)) {
                sort($skippedPages);
                $nextPage = $skippedPages[0];
                return redirect()->route('common.questions', ['id' => $brif->id, 'page' => $nextPage])
                    ->with('warning', 'У вас остались пропущенные вопросы. Пожалуйста, заполните их.');
            } else {
                // Если нет пропущенных страниц, завершаем бриф
                $brif->status = 'Завершенный';
                $brif->save();
                
                // Связываем с сделкой
                $deal = Deal::where('user_id', auth()->id())->first();
                if ($deal) {
                    $brif->deal_id = $deal->id;
                    $brif->save();
                    
                    $deal->common_id = $brif->id;
                    $deal->update([
                        'client_name'   => auth()->user()->name,
                        'client_phone'  => auth()->user()->phone ?? 'N/A',
                        'total_sum'     => $brif->price ?? 0,
                        'status'        => 'Бриф прикриплен',
                        'link'          => "/common/{$brif->id}",
                    ]);
                }
                
                return redirect()->route('deal.user')
                    ->with('success', 'Бриф успешно заполнен!');
            }
        }
        
        // Если достигнут конец всех страниц (что маловероятно, но для защиты)
        return redirect()->route('brifs.index')
            ->with('success', 'Все вопросы заполнены.');
    }

    /**
     * Пропустить текущую страницу брифа.
     *
     * @param  int  $id    ID брифа
     * @param  int  $page  Номер страницы
     * @return \Illuminate\Http\JsonResponse
     */
    public function skipPage($id, $page)
    {
        try {
            // Находим бриф по ID и текущему пользователю
            $brif = Common::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$brif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Бриф не найден или не принадлежит текущему пользователю.',
                ], 404);
            }
            
            // Пропускаем только если страница меньше 5
            if ((int)$page < 5) {
                // Получаем текущий список пропущенных страниц
                $skippedPages = json_decode($brif->skipped_pages ?? '[]', true);
    
                // Добавляем текущую страницу в список пропущенных, если её ещё нет
                if (!in_array((int)$page, $skippedPages)) {
                    $skippedPages[] = (int)$page;
                    $brif->skipped_pages = json_encode($skippedPages);
                }
    
                // Определяем следующую страницу
                $nextPage = (int)$page + 1;
    
                // Обновляем текущую страницу в брифе
                $brif->current_page = $nextPage;
                $brif->save();
    
                return response()->json([
                    'success' => true,
                    'redirect' => route('common.questions', ['id' => $brif->id, 'page' => $nextPage]),
                    'message' => 'Страница успешно пропущена.'
                ]);
            } else {
                // Страницу 5 и выше нельзя пропустить
                return response()->json([
                    'success' => false,
                    'message' => 'Эту страницу нельзя пропустить.'
                ], 400);
            }
        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            \Illuminate\Support\Facades\Log::error('Ошибка пропуска страницы: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при пропуске страницы: ' . $e->getMessage()
            ], 500);
        }
    }
}
