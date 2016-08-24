@extends('admin.layout')

@section('title', 'Product')

@section('app', 'product')

{{-- 注入类别服务类 --}}
@if(! isset($categoryService))
    @inject('categoryService', 'App\Services\CategoryService')
@endif

@section('content')

    <div class="row" id="app">
        <div class="col-md-12">
            <div class="form-horizontal">
                <fieldset>
                    <div>
                        <legend class="">添加商品</legend>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">商品名</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" v-model="name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">SKU</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" disabled="disabled" v-model="sku">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">类别</label>
                        <div class="controls col-sm-2">
                            <select class="form-control input-xlarge" v-model="parentCategory">
                                @foreach($categoryService->parentCategory() as $parentCategory):
                                    <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="controls col-sm-2">
                            <select class="form-control input-xlarge" v-model="category">
                                <option v-for="child in showChildren" value="@{{ child['id'] }}">@{{ child['name'] }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">原价格</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" v-model="originalPrice">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">优惠价格</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" v-model="discountPrice">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">商品库存</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" v-model="stock">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">商品简介</label>
                        <div class="controls col-sm-4">
                            <input type="text" class="form-control" v-model="intro">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">商品图片</label>
                        <div class="controls col-sm-1">
                            <button class="btn btn-warning" v-on:click="selectImage(1)">选择图片</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-10">
                            {{-- 图标 --}}
                            <ul v-if="icon">
                                <li class="picture-list">
                                    <div class="picture-box">
                                        {{-- 图片列表 --}}
                                        <img class="picture-list-img" v-bind:src="icon.url" v-bind:alt="icon.name" />
                                    </div>
                                    <span class="picture-name">@{{ icon.name }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">商品轮播图</label>
                        <div class="controls col-sm-1">
                            <button class="btn btn-success" v-on:click="selectImage(2)">选择图片</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-10">
                            {{-- 轮播图 --}}
                            <ul v-if="banners">
                                <li class="picture-list" v-for="banner in banners">
                                    <div class="picture-box">
                                        {{-- 轮播图列表 --}}
                                        <img class="picture-list-img" v-bind:src="banner.url" v-bind:alt="banner.name" />
                                    </div>
                                    <span class="picture-name">@{{ banner.name }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <!-- Textarea -->
                        <label class="control-label col-sm-2">商品详情</label>
                        <div class="controls col-sm-6">
                            <div id="desc"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-2"></div>
                        <div class="controls col-sm-3">
                            <input v-on:click="confirm" type="button" class="btn btn-primary" value="提交">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

@endsection

@section('script')

@include('admin.picture.select')

<script>
    var vm = new Vue({
        el: "#app",
        data: {
            {{-- 字段 --}}
            name: "{{ $name or '' }}",
            sku: "{{ $sku }}",
            parentCategory: "",
            category: "",
            originalPrice: "",
            discountPrice: "",
            stock: "",
            intro: "",
            icon: "",
            banners: "",
            desc: "",
            {{-- End 字段 --}}
            type: "add",
            childrenList: [],
            showChildren: [],
        },
        ready: function() {
            // 编辑器
            $('#desc').summernote({
                height: 200,
                minHeight: null,
                maxHeight: null,
                focus: false
            });
        },
        watch: {
            parentCategory: function() {
                vm.showChildren = [];
                vm.getChildren(vm.parentCategory);
            },
        },
        methods: {
            httpPost: function(url, params, type) {
                this.$http.post( url, params, {
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    emulateJSON: true
                }).then( function(reponse) {

                    var returnData = reponse.data;
                    if(returnData.err == 1) {

                        if(type == 'getChildrenList') {

                            vm.childrenList[params.id] = returnData.extra;

                            vm.showChildren = this.childrenList[params.id];
                        } else if(type == "") {

                        }

                    } else {

                        swal("出错了！", returnData.msg, "error");

                    }
                }, function(reponse) {

                    swal("出错了！", "数据传输错误", "error");
                });
            },
            getChildren: function(id) {
                {{-- 若不存在父类的子类，获取其的子类列表，并存到数组中 --}}
                if(typeof(this.childrenList[id]) === 'undefined') {
                    var url = "{{ url('category/children') }}";
                    var params = {
                        'id': id
                    };
                    var type = "getChildrenList";

                    this.httpPost(url, params, type);
                } else {
                    this.showChildren = this.childrenList[id];
                }

            },
            selectImage: function(source) {
                var pictureType = 1;
                var limit = 1;
                if(source == 2)
                    limit = 5;
                var selectPictures = [];

                {{-- source:来源(图标或轮播图)
                     pictureType:图片类型(产品)
                     limit:限制数量(最多可选)
                     selectPictures:已选的图片(用于编辑页面非新增页面)
                --}}

                var options = {
                    source: source,
                    pictureType: pictureType,
                    limit: limit,
                    selectPictures: selectPictures,
                }
                selectVm.show(options);
                return false;
            },
            confirm: function() {

            },
        }
    });

    selectVm.$watch('selectPictures', function() {
        {{-- 图片列表更改，显示到页面上 --}}
        if(selectVm.source == 1)
            vm.icon = selectVm.selectPictures[0];
        else
            vm.banners = selectVm.selectPictures;
    });
</script>
@endsection