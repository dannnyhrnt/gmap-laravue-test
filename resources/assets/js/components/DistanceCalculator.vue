<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Distance Calculator</div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="" class="form-inline">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="">From </label>
                                                <gmap-autocomplete
                                                    :value="orig.name"
                                                    @place_changed="setOrigin">
                                                </gmap-autocomplete>
                                                &nbsp;
                                                <label for="">To </label>
                                                <gmap-autocomplete
                                                    :value="dest.name"
                                                    @place_changed="setDestination">
                                                </gmap-autocomplete>
                                                &nbsp;
                                                <label for="">Using </label>
                                                <select class="form-control" v-model="type">
                                                    <option value="motorcycle">Motorcycle</option>
                                                    <option value="car">Car</option>
                                                    <option value="walking">Walking</option>
                                                </select>
                                                <button type="button" class="send-btn btn btn-primary" v-on:click="getDistance($event)">Calculate</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">

                            </div>
                        </div>

                        <div class="row" v-if="result.distance">
                            <div class="col-md-4">
                                Distance
                            </div>
                            <div class="col-md-8">
                                {{ result.distance }} KM
                            </div>
                        </div>

                        <div class="row" v-if="result.duration">
                            <div class="col-md-4">
                                Duration
                            </div>
                            <div class="col-md-8">
                                {{ result.duration }} Menit
                            </div>
                        </div>

                        <div class="row" v-if="result.cost">
                            <div class="col-md-4">
                                Cost
                            </div>
                            <div class="col-md-8">
                                Rp {{ result.cost }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Vue from 'vue';
    import * as VueGoogleMaps from 'vue2-google-maps'
    Vue.use(VueGoogleMaps, {
        load: {
        key: 'AIzaSyBlBJtebmohR1n5R84qifoH8_1iPzUoP2k',
        libraries: 'places', // This is required if you use the Autocomplete plugin
        // OR: libraries: 'places,drawing'
        // OR: libraries: 'places,drawing,visualization'
        // (as you require)
        }
    })
    export default {
        data () {
            return {
                orig: {
                    name: '',
                    latLng: {}
                },
                dest: {
                    name: '',
                    latLng: {}
                },
                result: {
                    distance: '',
                    duration: '',
                    cost: ''
                },
                type: ''
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        methods: {
            setOrigin(place) {
                this.orig.latLng = {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng()
                }
                // this.$refs.map.fitBounds();
                // var bounds = getBounds(this.$refs.map.getCurrentMarkers)
                // this.$refs.map.fitBounds(bounds)
            },
            setDestination(place) {
                this.dest.latLng = {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng()
                }
                // this.$refs.map.fitBounds();
            },
            getDistance(event) {
                event.preventDefault();
                let vm = this;
                $('.send-btn').html('Loading...');
                vm.result.distance = '';
                vm.result.duration = '';
                vm.result.cost = '';
                axios.post('http://localhost/popbox-test/public/api/distance/calc', `
                    { 
                        "origin":[ 
                            { 
                                "latitude":"`+ vm.orig.latLng.lat +`",
                                "longitude":"`+ vm.orig.latLng.lng +`"
                            }
                        ],
                        "destination":[ 
                            { 
                                "latitude":"`+ vm.dest.latLng.lat +`",
                                "longitude":"`+ vm.dest.latLng.lng +`"
                            }
                        ],
                        "vehicle": [
                            {
                                "type":"`+ vm.type +`",
                                "distance_per_litre":25,
                                "price_per_litre":6500         
                            }      
                        ]       
                    }
                `, {
                    headers: { 'Content-Type': 'application/json' }
                }).then(res => {
                    // console.log(res);
                    let vm = this;
                    let data = res.data;
                    // console.log(data);
                    if (!data.status) {
                        vm.result.distance = data.distance;
                        vm.result.duration = data.duration;
                        if (data.cost)
                            vm.result.cost = data.cost.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                        else
                            vm.result.cost = '';
                    }
                    $('.send-btn').html('Send');
                });;
            }
        }
    }
</script>
