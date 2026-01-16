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

    {{-- Subcategory/Product Type Filter --}}
    @php
        $currentCategoryCode = $collections['category']->categorycode ?? '';
        $subcategorycnt = count($subcategory);
        // "All" is active when no subcategory filter is applied
        $selectedSubcategory = request()->get('category', '');
        $isAllActive = empty($selectedSubcategory);
        // Check if we're on the "all" category page
$isAllCategoryPage = $currentCategoryCode == 'all' || empty($currentCategoryCode);
    @endphp

    @if ($subcategorycnt > 0)
        <div class="filter-item">
            <div class="filter-content">
                <ul class="list-unstyled cat-filters">
                    {{-- "All" link --}}
                    @if (!empty($currentCategoryCode))
                        <li {!! $isAllActive ? "class='active'" : '' !!}>
                            <a
                                href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $currentCategoryCode]) }}">
                                {{ strtolower(trans('common.All')) }}
                            </a>
                        </li>
                    @endif

                    {{-- Subcategory links --}}
                    @foreach ($subcategory as $row)
                        @php
                            $active = '';
                            $selectedSubcategory = request()->get('category', '');
                            // Check if this subcategory is currently active
                            if ($row->parentid == 0) {
                                // Main category - check query parameter
                                if ($selectedSubcategory == $row->categorycode) {
                                    $active = "class='active'";
                                }
                            } else {
                                // Child category - check if current category code matches or query parameter
                                if ($categoryCode == $row->categorycode || $selectedSubcategory == $row->categorycode) {
                                    $active = "class='active'";
                                }
                            }
                            $subcategoryName =
                                $locale == 'ar' ? $row->categoryAR ?? $row->category : $row->category ?? '';
                        @endphp
                        {{-- Matching CI logic: for main categories (parentid == 0), show if productcnt > 0 && subcategorycnt > 1 --}}
                        {{-- For "all" category page, show all categories regardless of product count --}}
                        {{-- For child categories, show if productcnt > 0 --}}
                        @if ($row->parentid == 0)
                            {{-- Main category: on "all" page, show all; otherwise show if has products AND there are multiple subcategories --}}
                            @if ($isAllCategoryPage || (($row->productcnt ?? 0) > 0 && $subcategorycnt > 1))
                                <li {!! $active !!}>
                                    <a
                                        href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $currentCategoryCode]) }}?category={{ $row->categorycode }}">
                                        {{ $subcategoryName }}
                                    </a>
                                </li>
                            @endif
                        @else
                            {{-- Child category: show if has products --}}
                            @if (($row->productcnt ?? 0) > 0)
                                <li {!! $active !!}>
                                    <a
                                        href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $row->categorycode]) }}">
                                        {{ $subcategoryName }}
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Colors Filter --}}
    @if (count($colorsArr) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Colors') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled color-filter">
                    @foreach ($colorsArr as $value)
                        @php
                            $checked = '';
                            if (in_array($value->filtervaluecode, $arrColor)) {
                                $checked = 'checked';
                            }
                            $colorName =
                                $locale == 'ar'
                                    ? $value->filtervalueAR ?? $value->filtervalue
                                    : $value->filtervalue ?? '';
                        @endphp
                        <li class="checkbox">
                            <label>
                                <input type="checkbox" name="color" class="color" {{ $checked }}
                                    value="{{ $value->filtervaluecode }}">
                                {{ $colorName }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Price Range Filter --}}
    @if (count($pricerange) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Price range') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled price-filter">
                    @foreach ($pricerange as $value)
                        @php
                            $checked = '';
                            if (isset($value->price) && $value->price == $price_qry) {
                                $checked = 'checked';
                            }
                            $priceText = $value->text ?? ($value->range ?? '');
                        @endphp
                        <li class="radio">
                            <label>
                                <input type="radio" name="price" class="price" {{ $checked }}
                                    value="{{ $value->price ?? ($value->range ?? '') }}">
                                {{ $priceText }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Sizes Filter --}}
    @if (count($sizesArr) > 0)
        <div class="filter-item">
            <h4 class="filter-heading">{{ trans('common.Size') }}</h4>
            <div class="filter-content">
                <ul class="list-unstyled color-filter">
                    @foreach ($sizesArr as $value)
                        @php
                            $checked = '';
                            if (in_array($value->filtervaluecode, $arrSize)) {
                                $checked = 'checked';
                            }
                            $sizeName =
                                $locale == 'ar'
                                    ? $value->filtervalueAR ?? $value->filtervalue
                                    : $value->filtervalue ?? '';
                        @endphp
                        <li class="checkbox">
                            <label>
                                <input type="checkbox" name="size" class="size" {{ $checked }}
                                    value="{{ $value->filtervaluecode }}">
                                {{ $sizeName }} ({{ $value->cnt ?? 0 }})
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
