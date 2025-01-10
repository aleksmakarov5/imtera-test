
<style>
.select{
    padding: 15px;
}
.select input{
    margin-left: 10px;
    margin-right: 3px;
}
.uravn{
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.itog
{
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgb(0, 47, 255);
    color: white;
}
</style>
<template>
<div>
    Выберите степень уравнения
</div>
<div class="select">
<input type="radio" name="nn" @click="nn=2">2
<input type="radio" name="nn" @click="nn=3">3
<input type="radio" name="nn" @click="nn=4">4
</div>
<div class="uravn">
    <div v-if="nn==0">
    Не выбрана степень уравнения
</div>

<div v-if="nn==2">
    Уравнение вида <br/>
    <math>
    <mi>a</mi>
    <mi>X</mi>
    <sup>
        <mn>2</mn>
    </sup>
    <mo>+</mo>
    <mi>b</mi>
    <mi>X</mi>
    <mo>+</mo>
    <mi>c</mi>
    <mo>=</mo>
    <mn>0</mn>
    </math>
    <br/>
    Введите коэффициенты в соответсвующие поля и нажмите кнопку Рассчитать
    <form  action="/step2" method="post">
        <input type="hidden" name="_token" :value="csrf">
        <input type="text" placeholder="a" name="a" size="3">
        <math>
        <mi>X</mi>
    <sup>
        <mn>2</mn>
    </sup>
    <mo>+</mo></math>
        <input type="text" placeholder="b" name="b" size="3">
        <math>
        <mi>X</mi>

    <mo>+</mo></math>
        <input type="text" placeholder="c" name="c" size="3">
        <math>


    <mo>=</mo><mi>0</mi></math>
        <br>
        <button type="submit" class="itog">Рассчитать</button>
    </form>
</div>
<div v-if="nn==3">3</div>
<div v-if="nn==4">4</div>
</div>
</template>

<script>

export default {
    props: [

    ],

    data() {

        return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            nn: 0,



        }
    },

    mounted() {
        this.nn = 0;
    },
    methods: {


    }
}



</script>
