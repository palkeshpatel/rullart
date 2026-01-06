@php
    $colorsArr = $collections['colorsArr'] ?? [];
    $sizesArr = $collections['sizesArr'] ?? [];
    $pricerange = $collections['pricerange'] ?? [];
    $subcategory = $collections['subcategory'] ?? [];
    $categoryCode = $categoryCode ?? '';
    $arrColor = $arrColor ?? [];
    $arrSize = $arrSize ?? [];
@endphp

<div class="catalog-filters">
    <a id="closeFilters" class="close-filters" href="javascript:;">
        <svg class="icon icon-close">
            <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
        </svg>
    </a>
    <h3 class="filters-heading">{{ trans('common.Refine By') }}</h3>
    
    {{-- "All" link --}}
    @php
        // Get current category code for "all" link
        $currentCategoryCode = $collections['category']->categorycode ?? '';
        
        // Check if "all" should be active (when viewing the main category without subcategory filter)
        $isAllActive = ($categoryCode == $currentCategoryCode);
    @endphp
    
    @if(!empty($currentCategoryCode))
        <div class="filter-item">
            <div class="filter-content">
                <ul class="list-unstyled cat-filters">
                    <li {!! $isAllActive ? "class='active'" : '' !!}>
                        <a href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $currentCategoryCode]) }}">
                            {{ strtolower(trans('common.All')) }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
    
    {{-- Colors Filter --}}
    @if(count($colorsArr) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Colors') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled color-filter">
                    @foreach($colorsArr as $value)
                        @php
                            $checked = '';
                            if (in_array($value->filtervaluecode, $arrColor)) {
                                $checked = 'checked';
                            }
                            $colorName = $locale == 'ar' ? ($value->filtervalueAR ?? $value->filtervalue) : ($value->filtervalue ?? '');
                        @endphp
                        <li class="checkbox">
                            <label>
                                <input type="checkbox" name="color" class="color" {{ $checked }} value="{{ $value->filtervaluecode }}">
                                {{ $colorName }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    
    {{-- Price Range Filter --}}
    @if(count($pricerange) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Price range') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled price-filter">
                    @foreach($pricerange as $value)
                        @php
                            $checked = '';
                            if (isset($value->price) && $value->price == $price_qry) {
                                $checked = 'checked';
                            }
                            $priceText = $value->text ?? $value->range ?? '';
                        @endphp
                        <li class="radio">
                            <label>
                                <input type="radio" name="price" class="price" {{ $checked }} value="{{ $value->price ?? $value->range ?? '' }}">
                                {{ $priceText }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    
    {{-- Sizes Filter --}}
    @if(count($sizesArr) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Size') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled color-filter">
                    @foreach($sizesArr as $value)
                        @php
                            $checked = '';
                            if (in_array($value->filtervaluecode, $arrSize)) {
                                $checked = 'checked';
                            }
                            $sizeName = $locale == 'ar' ? ($value->filtervalueAR ?? $value->filtervalue) : ($value->filtervalue ?? '');
                        @endphp
                        <li class="checkbox">
                            <label>
                                <input type="checkbox" name="size" class="size" {{ $checked }} value="{{ $value->filtervaluecode }}">
                                {{ $sizeName }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

