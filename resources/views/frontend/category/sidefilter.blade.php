@php
    $colorsArr = $collections['colorsArr'] ?? [];
    $sizesArr = $collections['sizesArr'] ?? [];
    $pricerange = $collections['pricerange'] ?? [];
    $subcategory = $collections['subcategory'] ?? [];
    $categoryCode = $categoryCode ?? '';
    $colors_qry = request()->get('color', '');
    $sizes_qry = request()->get('size', '');
    $price_qry = request()->get('price', '');
    
    $arrColor = [];
    if ($colors_qry != '') {
        $arrColor = explode(',', $colors_qry);
    }
    $arrSize = [];
    if ($sizes_qry != '') {
        $arrSize = explode(',', $sizes_qry);
    }
@endphp

<div class="catalog-filters">
    <a id="closeFilters" class="close-filters" href="javascript:;">
        <svg class="icon icon-close">
            <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
        </svg>
    </a>
    <h3 class="filters-heading">{{ __('Refine By') }}</h3>
    
    {{-- Subcategories Filter --}}
    <div class="filter-item">
        <div class="filter-content">
            <ul class="list-unstyled cat-filters">
                @if(!empty($subcategory))
                    @php
                        $subcategorycnt = count($subcategory);
                    @endphp
                    
                    @foreach($subcategory as $row)
                        @php
                            $active = '';
                            if ($categoryCode == $row->categorycode) {
                                $active = "class='active'";
                            }
                            $catName = $locale == 'ar' ? ($row->categoryAR ?? $row->category) : ($row->category ?? '');
                        @endphp
                        
                        @if($row->parentid == 0)
                            @if($row->productcnt > 0 && $subcategorycnt > 1)
                                <li {!! $active !!}>
                                    <a href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $row->categorycode]) }}">
                                        {{ $catName }}
                                    </a>
                                </li>
                            @endif
                        @else
                            @if($row->productcnt > 0)
                                <li {!! $active !!}>
                                    <a href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $row->categorycode]) }}">
                                        {{ $catName }}
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
    
    {{-- Colors Filter --}}
    @if(count($colorsArr) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ __('Colors') }}</h4>
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
            <h4 class="filter-heading">{{ __('Price range') }}</h4>
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
            <h4 class="filter-heading">{{ __('Size') }}</h4>
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

