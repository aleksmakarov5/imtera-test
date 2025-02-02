
<style>
.accordion-est
{
    background-color: blue;
    color: white;
    cursor: pointer;
    padding: 12px 16px;
    text-decoration: none;
    border: none;
    margin: 0;
    display: block;
    text-align: center;
    font-weight: bold;
    transition: 0.3s;
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    z-index: 1;
    position: relative;
    overflow: hidden;
    user-select: none;
    border-radius: 5px;
}

.accordion-est:hover
{
    background-color: #000980;
    color: white;
    font-size: large;
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    transition: 0.3s;
    z-index: 1;
    position: relative;
    overflow: hidden;
    user-select: none;
    border-radius: 5px;

}
.d_none
{
    display: none;
}
.cont
{
    padding: 16px;
    text-align: left;
    background-color: #ffffff;
    overflow: hidden;
    transition: 0.3s;
    border-radius: 5px;
    margin-top: 10px;
    position: relative;
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
    z-index: 0;
    user-select: none;
}
</style>
<template>
<div>
    <button class="accordion-est" @click="saveShear()">
            Сохранить
        </button>
        <button class="accordion-est" @click="loadShear()">
            Загрузить
        </button>
        <button class="accordion-est" @click="show_wwod=!show_wwod">
            Исходные данные
        </button>
        <div  :class="show_wwod?'':'d_none'">
            <div class="cont">
<div class="row">
    <div class="col-md-2">
        <h3>Количество ребер</h3>
        <input type="text" v-model="M" @blur="makeTable()">
        </div>
        <div class="col-md-2">
        <h3>Количество контуров</h3>
        <input type="text" v-model="K" @blur="makeTable()">
        </div>
        <div class="row" v-if="M!=0&&K!=0">
            <div class="col">
                <h3>Координаты вершин</h3>
                <table class="table">
                    <tr>
                        <th>Номер</th>
                        <th>Z</th>
                        <th>Y</th>
                    </tr>
                    <tr v-for="(item, index) in z" :key="index">
                        <td>{{ index+1 }}</td>
                        <td contenteditable :id='"Z_"+index' @blur="editZ(index)">{{ z[index] }}</td>
                        <td contenteditable :id='"Y_"+index' @blur="editY(index)">{{ y[index] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col">
                <h3>Матрица вершин</h3>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Номер ребра</th>
                        <th>Начало</th>
                        <th>Конец</th>
                        <th>Толщина</th>
                        <th>Коэффициент</th>
                        <th :colspan="K">Матрица контуров</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in c" :key="index">
                        <td>{{ index+1 }}</td>
                        <td contenteditable :id='"I1_"+index' @blur="editI1(index)">{{ i1[index] }}</td>
                        <td contenteditable :id='"I2_"+index' @blur="editI2(index)">{{ i2[index] }}</td>
                        <td contenteditable :id='"H_"+index' @blur="editH(index)">{{ h[index] }}</td>
                        <td contenteditable :id='"K_"+index' @blur="editK(index)">{{ k[index] }}</td>


                            <td v-for="(item1,index2) in item" contenteditable :id="'C_'+index+'_'+index2" @blur="editC(index,index2)">
                                {{ c[index][index2] }}

                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

</div>
            </div>
        </div>
        <button class="accordion-est" @click="show_paint=!show_paint">
            Графическое представление профиля
        </button>
        <div  :class="show_paint?'':'d_none'">
            <button class="accordion-est" @click="paintCanvas()">
            Показать
        </button>
            <div class="cont">
                <center>
                <canvas id="myCanvas" :width="maxY+10" :height="maxZ+10"></canvas></center>
            </div>
        </div>
        <button class="accordion-est" @click="show_result=!show_result">
            Результат
        </button>
        <div  :class="show_result?'':'d_none'">
            <div class="cont">
Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint, aperiam enim! Deserunt, dolor tenetur! Delectus necessitatibus vitae expedita amet vel fugiat ratione, perspiciatis dolor qui, earum consequuntur. Numquam, dolorem eius!

            </div>
        </div>

</div>

</template>

<script>

export default {
    props: [

    ],

    data() {

        return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            show_wwod: false,
            show_paint: false,
            show_result: false,
            maxZ: 0,
            maxY: 0,
            nn: 0,
            M: 0,
            K: 0,
            z: [],
            y: [],
            i1: [],
            i2: [],
            tm: [],
            k: [],
            c: [],
            h: [],

        }
    },

    mounted() {
        this.nn = 0;
    },
    methods: {

        makeTable()
        {
            this.z = [];
            this.y = [];
            this.i1 = [];
            this.i2 = [];
            this.tm = [];
            this.k = [];
            this.c = [];
            this.h = [];

            if(this .m !=0&&this.K!=0)
                for (let i = 0; i < (this.M - this.K + 1); i++) {
                    if (!this.z[i])
                        this.z[i] = 0
                if (!this.y[i])
                    this.y[i]=0
            }
            for (let i = 0; i < this.M; i++) {
                this.i1[i]=0
                this.i2[i] = 0
                this.tm[i] = 0
                this.k[i] = 0
                this.h[i] = 0
                this.c[i] = []
                for (let j = 0; j < this.K; j++) {
                    this.c[i][j]=0
                }
            }


        },
        editZ(index)
        {
            this.z[index] = parseFloat(document.getElementById('Z_' + index).innerText)
            for (let i = 0; i < (this.M - this.K + 1); i++) {
                if (this.maxZ < this.z[i])
                    this.maxZ = this.z[i];
            }


        },
        editY(index)
        {
            this.y[index] = parseFloat(document.getElementById('Y_' + index).innerText)
            for (let i = 0; i < (this.M - this.K + 1); i++) {
                if (this.maxY < this.y[i])
                    this.maxY = this.y[i];
            }

        },
        editI1(index)
        {
            this.i1[index] = parseInt(document.getElementById('I1_' + index).innerText);
            this.paintCanvas()
        },
        editI2(index)
        {
            this.i2[index] = parseInt(document.getElementById('I2_'+index).innerText);
            this.paintCanvas()
        },
        editC(index1, index2)
        {
            this.c[index1][index2] = document.getElementById('C_'+index1+'_'+index2).innerText;
        },
        editH(index)
        {
            this.h[index] = document.getElementById('H_'+index).innerText;
        },
        editK(index)
        {
            this.k[index] = document.getElementById('K_'+index).innerText;
        },
        paintCanvas()
        {

            const canvas = document.getElementById('myCanvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
            for (let i = 0; i <=this.M; i++) {
                if (this.i1[i] > 0 && this.i2[i] > 0)
                {
                    ctx.moveTo(this.y[this.i1[i] - 1]+5, this.maxZ-this.z[this.i1[i] - 1]);

                    ctx.lineTo(this.y[this.i2[i] - 1]+5, this.maxZ-this.z[this.i2[i] - 1]);

                }
            }
            ctx.strokeStyle = "red";
            ctx.stroke();



        },
        saveShear() {

            axios.post(`/api/shear_save`, {
                K: this.K,
                M: this.M,
                z: this.z,
                y: this.y,
                i1: this.i1,
                i2: this.i2,

            })
                .then(res => {
                    console.log(res.data)
                },
                error => {
                    this.errors=error.response.data.errors
                }
                )
        },
        loadShear() {
            axios.get(`/api/shear_load`)
                .then(res => {
                     this.M = res.data[0].M;
                    this.K = res.data[0].K;
                    this.z = []
                    this.y = []
                    for (let i = 0; i < res.data[1].length; i++)
                    {
                        this.z[i] = parseFloat(res.data[1][i].z);
                        this.y[i] = parseFloat(res.data[1][i].y);
                    }
                    for (let i = 0; i < res.data[2].length; i++)
                    {
                        this.i1[i] = parseFloat(res.data[2][i].i1);
                        this.i2[i] = parseFloat(res.data[2][i].i2);
                        this.tm[i] = 0
                        this.k[i] = 0
                        this.h[i] = 0
                        this.c[i] = []

                for (let j = 0; j < this.K; j++) {
                    this.c[i][j]=0
                }
                    }
                    console.log(this.M, this.K, this.z[5], this.y[5])
for (let i = 0; i < (this.M - this.K + 1); i++) {
                if (this.maxZ < this.z[i])
                    this.maxZ = this.z[i];
    if (this.maxY < this.y[i])
                this.maxY = this.y[i];
            }


                    this.paintCanvas()
                },
                error => {
                    this.errors=error.response.data.errors
                }
                )
        },



    }
}



</script>
