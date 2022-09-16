@extends('layouts.app')
@section('title', '商品列表')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-body">
                    <!-- 筛选组件开始 -->
                    <form action="{{ route('products.index') }}" class="search-form">
                        <!-- 创建一个隐藏字段 -->
                        <input type="hidden" name="filters">
                        <div class="form-row">
                            <div class="col-md-9">
                                <div class="form-row">
                                    @if ($category)
                                        .
                                        .
                                        .
                                    @endif
                                <!-- 商品属性面包屑开始 -->
                                    <!-- 遍历当前属性筛选条件 -->
                                    @foreach($propertyFilters as $name => $value)
                                        <span class="filter">{{ $name }}:
            <span class="filter-value">{{ $value }}</span>
                                            <!-- 调用之后定义的 removeFilterFromQuery -->
            <a class="remove-filter" href="javascript: removeFilterFromQuery('{{ $name }}')">×</a>
          </span>
                                @endforeach
                                <!-- 商品属性面包屑结束 -->
                                    <div class="col-auto"><input type="text" class="form-control form-control-sm"
                                                                 name="search" placeholder="搜索"></div>
                                    <div class="col-auto">
                                        <button class="btn btn-primary btn-sm">搜索</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="order" class="form-control form-control-sm float-right">
                                    <option value="">排序方式</option>
                                    <option value="price_asc">价格从低到高</option>
                                    <option value="price_desc">价格从高到低</option>
                                    <option value="sold_count_desc">销量从高到低</option>
                                    <option value="sold_count_asc">销量从低到高</option>
                                    <option value="rating_desc">评价从高到低</option>
                                    <option value="rating_asc">评价从低到高</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <!-- 筛选组件结束 -->


                    <div class="filters">
                        @if ($category && $category->is_directory)
                            .
                            .
                            .
                        @endif
                    <!-- 分面搜索结果开始 -->
                        <!-- 遍历聚合的商品属性 -->
                        @foreach($properties as $property)
                            <div class="row">
                                <!-- 输出属性名 -->
                                <div class="col-3 filter-key">{{ $property['key'] }}：</div>
                                <div class="col-9 filter-values">
                                    <!-- 遍历属性值列表 -->
                                @foreach($property['values'] as $value)
                                    <!-- 调用下面定义的 appendFilterToQuery 函数 -->
                                        <a href="javascript: appendFilterToQuery('{{ $property['key'] }}', '{{ $value }}')">{{ $value }}</a>
                                    @endforeach
                                </div>
                            </div>
                    @endforeach
                    <!-- 分面搜索结果结束 -->
                    </div>


                    <div class="row products-list">
                        @foreach($products as $product)
                            <div class="col-3 product-item">
                                <div class="product-content">
                                    <div class="top">
                                        <div class="img">
                                            <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                                <img src="{{ $product->image_url }}" alt="">
                                            </a>
                                        </div>
                                        <div class="price"><b>￥</b>{{ $product->price }}</div>
                                        <div class="title">
                                            <a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="float-right">{{ $products->links() }}</div>  <!-- 只需要添加这一行 -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        var filters = {!! json_encode($filters) !!};
        $(document).ready(function () {
            $('.search-form input[name=search]').val(filters.search);
            $('.search-form select[name=order]').on('change', function () {
                $('.search-form').submit();
            });

            // 之前监听的切换排序方式事件
            $('.search-form select[name=order]').on('change', function() {
                // 解析当前查询参数
                var searches = parseSearch();
                // 如果有属性筛选
                if (searches['filters']) {
                    // 将属性筛选值放入隐藏字段中
                    $('.search-form input[name=filters]').val(searches['filters']);
                }
                $('.search-form').submit();
            });
        })

        // 定义一个函数，用于解析当前 Url 里的参数，并以 Key-Value 对象形式返回
        function parseSearch() {
            // 初始化一个空对象
            var searches = {};
            // location.search 会返回 Url 中 ? 以及后面的查询参数
            // substr(1) 将 ? 去除，然后以符号 & 分割成数组，然后遍历这个数组
            location.search.substr(1).split('&').forEach(function (str) {
                // 将字符串以符号 = 分割成数组
                var result = str.split('=');
                // 将数组的第一个值解码之后作为 Key，第二个值解码后作为 Value 放到之前初始化的对象中
                searches[decodeURIComponent(result[0])] = decodeURIComponent(result[1]);
            });

            return searches;
        }

        // 根据 Key-Value 对象构建查询参数
        function buildSearch(searches) {
            // 初始化字符串
            var query = '?';
            // 遍历 searches 对象
            _.forEach(searches, function (value, key) {
                query += encodeURIComponent(key) + '=' + encodeURIComponent(value) + '&';
            });
            // 去除最末尾的 & 符号
            return query.substr(0, query.length - 1);
        }

        // 将新的 filter 追加到当前的 Url 中
        function appendFilterToQuery(name, value) {
            // 解析当前 Url 的查询参数
            var searches = parseSearch();
            // 如果已经有了 filters 查询
            if (searches['filters']) {
                // 则在已有的 filters 后追加
                searches['filters'] += '|' + name + ':' + value;
            } else {
                // 否则初始化 filters
                searches['filters'] = name + ':' + value;
            }
            // 重新构建查询参数，并触发浏览器跳转
            location.search = buildSearch(searches);
        }
        // 将某个属性 filter 从当前查询中移除
        function removeFilterFromQuery(name) {
            // 解析当前 Url 的查询参数
            var searches = parseSearch();
            // 如果没有 filters 查询则什么都不做
            if(!searches['filters']) {
                return;
            }

            // 初始化一个空数组
            var filters = [];
            // 将 filters 字符串拆解
            searches['filters'].split('|').forEach(function (filter) {
                // 解析出属性名和属性值
                var result = filter.split(':');
                // 如果当前属性名与要移除的属性名一致，则退出
                if (result[0] === name) {
                    return;
                }
                // 否则将这个 filter 放入之前初始化的数组中
                filters.push(filter);
            });
            // 重建 filters 查询
            searches['filters'] = filters.join('|');
            // 重新构建查询参数，并触发浏览器跳转
            location.search = buildSearch(searches);
        }
    </script>
@endsection
