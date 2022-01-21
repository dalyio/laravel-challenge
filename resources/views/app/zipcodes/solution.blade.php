@extends('layout.app')

@section('content')
<div class="d-flex flex-wrap p-3 gap-1">
    
    <div class="card card-widget card-codeblock" style="width: 100%">
        <div class="card-body">
            <div class="card-title">
                <span>{{ __('Haversine Formula') }}</span>
            </div>
            <div>
                <blockquote>
                  <pre><code>{{ $codeblock('haversine-formula') }}</code></pre>
                </blockquote>  
            </div>
        </div>
    </div>
    
    <div class="card card-widget" style="width: calc(50% - .5rem)">
        <div class="card-body">
            <div class="card-title">
                <span>{{ __('Distance By Zipcodes') }}</span>
            </div>
            <div id="zipcode-distance-component">
                <zipcode-distance-component></zipcode-distance-component>
            </div>
        </div>
    </div>
    
    <div class="card card-widget" style="width: calc(50% - .5rem)">
        <div class="card-body">
            <div class="card-title">
                <span>{{ __('Test The API') }}</span>
            </div>
            <div id="zipcode-distance-api-component">
                <zipcode-distance-api-component></zipcode-distance-api-component>
            </div>
        </div>
    </div>
    
</div>
@endsection

<script type="text/javascript">
    
    document.addEventListener('DOMContentLoaded', function() {
    
        const zipcodeDistanceVue = Vue.createApp({});
        zipcodeDistanceVue.component('zipcode-distance-component', {
            template: `
            
                <div class="form-group mb-0">
                    <div class="input-group">
                        <input id="zipcode-input" name="zipcode" type="text" class="form-control" v-model="zipcode">
                        <span class="input-group-addon"><btn id="zipcode-btn" class="btn btn-admin" v-on:click="addZipcode(zipcode)">{{ __('Add Zipcode') }}</btn></span>
                    </div>
                    <div v-if="suggestions.length" class="suggestion-results" role="listbox">
                        <div v-for="suggestion in suggestions" v-text="suggestion.zipcode" class="result-link" v-on:click="addZipcode(suggestion.zipcode)"></div>
                    </div>
                </div>
                                  
                <div v-if="zipcodes.length" class="mt-3">
                    <table class="table table-widget">
                        <thead>
                            <tr class="font-weight-bold">
                                <td>Zipcode From</td>
                                <td>Zipcode To</td>
                                <td>Distance</td>
                            </tr>
                        </thead>
                        <tbody v-if="!distances.length">
                            <tr v-for="(zipcode, key) in zipcodes">
                                <td><span v-text="zipcode"></span></td>
                                <td></td>
                                <td></td>
                            </tr>            
                        </tbody>
                        <tbody v-if="distances.length">
                            <tr v-for="(distance, key) in distances">
                                <td v-if="distance.success"><span v-text="distance.zipcode_from.zipcode"></span></td>
                                <td v-if="distance.success"><span v-text="distance.zipcode_to.zipcode"></span></td>
                                <td v-if="distance.success"><span v-text="formatDistance(distance)"></span></td>
                                <td v-if="!distance.success" colspan="3"><span class="text-align-center"></span></td>
                            </tr>
                        </tbody>
                    </table>             
                    <div class="d-flex flex-fill">                        
                        <div v-if="zipcodes.length >= 2" class="mr-3">
                            <btn class="btn btn-admin" v-on:click="calculateDistance">{{ __('Calculate Distance') }}<btn>
                        </div>
                        <div v-if="zipcodes.length >= 1">                        
                            <btn v-if="zipcodes.length >= 1" class="btn btn-admin" v-on:click="reset">{{ __('Reset') }}<btn>
                        </div>
                    </div>          
                </div>
                                            
                                          
                <div v-if="awaiting" class="font-sm font-italic">
                    <span>{{ __('calculating') }}</span>
                    <span v-text="awaitingElipsis"></span>
                </div>
                <div v-if="isNumeric(timer)" class="font-sm font-italic">
                    <div v-if="timer < 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took less than 1 second to complete') }}</span>
                    </div>
                    <div v-if="timer == 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took') }}</span>
                        <span v-text="timer" class="mx-2"></span>
                        <span>{{ __('second to complete') }}</span>
                    </div>
                    <div v-if="timer > 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took') }}</span>
                        <span v-text="timer" class="mx-2"></span>
                        <span>{{ __('seconds to complete') }}</span>
                    </div>
                </div>
                                    
            
            `,
            props: [],
            data() {
                return {
                    zipcode: null,
                    zipcodes: [],
                    distances: [],
                    suggestions: [],
                    timer: null,
                    interval: null,
                    awaiting: false,
                    awaitingElipsis: '...',
                }
            },
            created() {
                this.$root.$refs.zipcodeDistanceVue = this;
            },
            mounted() {
                this.events();
            },
            watch: {
                zipcode: debounce(function(value, oldValue) {
                    if (value) {
                        this.search();
                    } else {
                        this.suggestions = [];
                    }
                }, 500),
            },
            methods: {
                formatDistance(distance) {
                    return (distance) ? distance.distance+' '+distance.unit : null;
                },
                isLast(link) {
                    return (link === this.zipcodes[this.zipcodes.length - 1]);
                },
                isNumeric(value) {
                    return (typeof value === 'number') ? true : false;
                },
                addZipcode(zipcode) {
                    if (typeof zipcode === 'string' && zipcode.length) {
                        this.zipcodes.push(zipcode);
                        this.suggestions = [];
                        this.zipcode = null;
                    }
                },
                pullDistance(key) {
                    if (this.distances[key]) {
                        return this.distances[key].distance+' '+this.distances[key].unit;
                    }
                },
                reset() {
                    this.zipcodes = [];
                    this.suggestions = [];
                    this.timer = null;
                },
                search() {
                    $.ajax({
                        url: {!! json_encode(route('zipcode-search')) !!},
                        type: 'POST',
                        data: {
                            search: this.zipcode
                        },
                        success: function(response) {
                            if (response.success) {
                                this.suggestions = response.results;
                            }
                            this.$forceUpdate();
                        }.bind(this)
                    });
                },
                calculateDistance() {
                    $.ajax({
                        url: {!! json_encode(route('zipcode-distance')) !!},
                        type: 'POST',
                        data: {
                            zipcodes: this.zipcodes
                        },
                        success: function(response) {
                            if (response.success) {
                                this.distances = response.data;
                            }
                            this.$forceUpdate();
                        }.bind(this)
                    });
                },
                events() {
                    var input = document.getElementById('zipcode-input');
                    input.addEventListener('keyup', function(event) {
                        if (event.keyCode === 13) {
                            event.preventDefault();
                            document.getElementById('zipcode-btn').click();
                        }
                    });
                },
            },
        });
        const zipcodeDistanceVueMount = zipcodeDistanceVue.mount('#zipcode-distance-component');
        
    });
</script>

<script type="text/javascript">
    
    document.addEventListener('DOMContentLoaded', function() {
    
        const zipcodeDistanceApiVue = Vue.createApp({});
        zipcodeDistanceApiVue.component('zipcode-distance-api-component', {
            template: `
            
                <div class="form-group mb-0">
                    <textarea id="zipcode-api-input" class="form-control" rows="10" v-model="input"></textarea>
                </div>
                                
                <div class="d-flex flex-row mt-3">                        
                    <div>
                        <btn id="zipcode-api-btn" class="btn btn-admin" v-on:click="calculateDistance">{{ __('Submit') }}<btn>
                    </div>
                </div>  
                                  
                <div v-if="distances.length" class="mt-3">
                    <table class="table table-widget">
                        <thead>
                            <tr class="font-weight-bold">
                                <td>Zipcode From</td>
                                <td>Zipcode To</td>
                                <td>Distance</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(distance, key) in distances">
                                <td v-if="distance.success"><span v-text="distance.zipcode_from.zipcode"></span></td>
                                <td v-if="distance.success"><span v-text="distance.zipcode_to.zipcode"></span></td>
                                <td v-if="distance.success"><span v-text="formatDistance(distance)"></span></td>
                                <td v-if="!distance.success" colspan="3"><center><span v-text="distance.message"></span></center></td>
                            </tr>
                        </tbody>
                    </table>       
                </div>
                                 
                <div v-if="awaiting" class="font-sm font-italic">
                    <span>{{ __('calculating') }}</span>
                    <span v-text="awaitingElipsis"></span>
                </div>
                <div v-if="isNumeric(timer)" class="font-sm font-italic">
                    <div v-if="timer < 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took less than 1 second to complete') }}</span>
                    </div>
                    <div v-if="timer == 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took') }}</span>
                        <span v-text="timer" class="mx-2"></span>
                        <span>{{ __('second to complete') }}</span>
                    </div>
                    <div v-if="timer > 1" class="d-flex flex-wrap">
                        <span>{{ __('Calculation took') }}</span>
                        <span v-text="timer" class="mx-2"></span>
                        <span>{{ __('seconds to complete') }}</span>
                    </div>
                </div>
            `,
            props: [],
            data() {
                return {
                    input: null,
                    distances: [],
                    message: null,
                    timer: null,
                    interval: null,
                    awaiting: false,
                    awaitingElipsis: '...',
                }
            },
            created() {
                this.$root.$refs.zipcodeDistanceApiVue = this;
            },
            mounted() {
                this.events();
            },
            watch: {
                
            },
            methods: {
                formatDistance(distance) {
                    return (distance) ? distance.distance+' '+distance.unit : null;
                },
                isNumeric(value) {
                    return (typeof value === 'number') ? true : false;
                },
                reset() {
                    this.timer = null;
                },
                calculateDistance() {
                    
                    try {
                         zipcodes = JSON.parse(this.input);
                    } catch (e) {
                        this.message = 'Please make sure JSON is properly structured';
                        return false;
                    }
                    
                    $.ajax({
                        url: {!! json_encode(route('api-zipcode-distance')) !!},
                        type: 'POST',
                        data: {
                            zipcodes: zipcodes
                        },
                        success: function(response) {
                            if (response.success) {
                                this.distances = response.distances;
                            }
                            this.$forceUpdate();
                        }.bind(this)
                    });
                },
                events() {
                    var input = document.getElementById('zipcode-api-input');
                    input.addEventListener('keyup', function(event) {
                        if (event.keyCode === 13) {
                            event.preventDefault();
                            document.getElementById('zipcode-api-btn').click();
                        }
                    });
                },
            },
        });
        const zipcodeDistanceApiVueMount = zipcodeDistanceApiVue.mount('#zipcode-distance-api-component');
        
    });
</script>
