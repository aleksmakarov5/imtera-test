
<template>
    <div>
        <div v-if="redactArrayNotNull()" style="position: absolute; right: 130px; top:80px;">
            Действия с выбранными операциями
            <div>
                <img src="storage/img/delete.png" title="Удалить" width="30"
                    style="cursor: pointer; display: inline; margin-right: 15px" @click="deleteTransaction()">
                <img src="storage/img/copy.png" title="Скопировать" width="23" style="cursor: pointer; display: inline;"
                    @click="copyTransaction()">
            </div>
        </div>
        <div v-if="!add_show" @click="add_show = !add_show" style="
        position: absolute;
        left: 520px;
        top: 90px;
        z-index: 5;
        cursor: pointer;
        ">
            <img src="storage/img/add.png" title="Добавить транзакцию" width="30">
        </div>
        <div v-if=" add_show" id="form_add" style="
        position: absolute;
        left: 10%;
        top: 15%;
        z-index: 5;
        background-color: white;
        border-radius: 10px;
        width: 80%;
        height: 80%;
        overflow: auto;
        padding: 10px;
        font-size: 14px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.7), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        ">
            <div @click="add_show = !add_show" style="
            position: absolute;
            right: 8px;
            top: 8px;
        cursor: pointer;
        z-index: 5
        ">
                <img src="storage/img/close.png" alt="" width="15">
            </div>
            <div class="form-group col-md-12">
                <div :class="'form-group ' + showDanger(errors.Kontragent)">
                    {{ showError(errors.Kontragent) }}
                    <label for="Kontragent">Контрагент</label>
                    <input type="text" class="form-control form-control-sm" id="Kontragent" v-model="Kontragent">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <div :class="'form-group ' + showDanger(errors.budget_item_id)">
                        {{ showError(errors.budget_item_id) }}
                        <label for="budget_item_id">Статья учета</label>
                        <select class="form-control form-control-sm" id="budget_item_id" v-model="budget_item_id">
                            <option value="1">-</option>
                            <option value="2">Услуги связи</option>
                            <option value="3">Логистические услуги</option>
                            <option value="4">Расходные материалы</option>
                            <option value="5">Аренда помещений</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div :class="'form-group ' + showDanger(errors.Type)">
                        {{ showError(errors.Type) }}
                        <label for="Type">Тип оплаты</label>
                        <select class="form-control form-control-sm" id="Type" v-model="Type">
                            <option value="0">Доход</option>
                            <option value="1">Расход</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <div :class="'form-group ' + showDanger(errors.Summ)">
                        {{ showError(errors.Summ) }}
                        <label for="Summ">Сумма</label>
                        <input type="text" class="form-control form-control-sm" id="Summ" v-model="Summ"
                            @keyup="Summ = Summ.replace(/[^0-9.]/g, '')">
                    </div>
                </div>
                <div class="form-group col-md-5">
                    <div :class="'form-group ' + showDanger(errors.Sch)">
                        {{ showError(errors.Sch) }}
                        <label for="Sch">Счет</label>
                        <input type="text" class="form-control form-control-sm" id="Sch" v-model="Sch"
                            @keyup="Summ = Summ.replace(/[^0-9]/g, '')">
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div :class="'form-group ' + showDanger(errors.Date)">
                        {{ showError(errors.Date) }}
                        <label for="Date">Дата оплаты</label>
                        <input type="date" class="form-control form-control-sm" id="Date" v-model="Date">
                    </div>
                </div>
            </div>

            <div :class="'form-group ' + showDanger(errors.NazPay)">
                {{ showError(errors.NazPay) }}
                <label for="NazPay">Назначение платежа</label>
                <textarea class="form-control form-control-sm" id="NazPay" rows="2" v-model="NazPay"></textarea>
            </div>


            <div class="form-row">
                <div class="form-group col-md-6">
                    <div :class="'form-group '+showDanger(errors.deal_id)">
                        {{ showError(errors.deal_id) }}
                        <label for="deal_id">Сделка</label>
                        <select class="form-control form-control-sm" id="deal_id">
                            <option value=1></option>
                            <option value=2>Продажа</option>
                            <option value=3>Покупка</option>
                            <option value=4>Аренда</option>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <div :class="'form-group ' + showDanger(errors.status_id)">
                        {{ showError(errors.status_id) }}
                        <label for="status_id">Статус</label>
                        <select class="form-control form-control-sm" id="status_id" v-model="status_id">
                            <option value=1></option>
                            <option value=0>Факт</option>
                        </select>
                    </div>
                </div>
            </div>


            <button class="btn btn-primary" @click="createTransaction()">Добавить</button>
        </div>
        <table class=" table table-bordered " style=" white-space: nowrap; font-size: 14px;">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" name="all" id="all" @change="allChecked()">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Контрагент
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статья
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Сумма
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Счет
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Дата оплаты
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Описание
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Сделка
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(transaction,index) in transactions_date">
                    <tr>
                        <td colspan="9" style="font-size: 16px; font-style: solid;">
                            <b>{{ fortmatDate(index) }}</b>

                        </td>

                    </tr>
                    <tr v-for="tr in transaction">
                        <td>
                            <input type="checkbox" :name="'select_'+tr.id" :id="'select_' + tr.id"
                                @change="addRedact(tr.id)">
                        </td>
                        <td><span v-html="tr.Kontragent" /></td>
                        <td>{{ budgetItem(tr.Type) }}</td>
                        <td :style="typePlat(tr.Type)"><span v-if="tr.Type==1">-</span>{{ makeMoney(tr.Summ) }}
                        </td>
                        <td>{{ tr.Sch }}</td>
                        <td>{{ fortmatDateShort(tr.Date) }}</td>
                        <td><span v-html="tr.NazPay" /></td>
                        <td>{{ tr.deal_id }}</td>
                        <td><label class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>Факт</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>

export default {
    props: [
        'in_transactions_date'
    ],

    data() {

        return {
            transactions_date: this.in_transactions_date,
            redactArray: [],
            add_show: false,
            Kontragent: '',
            Type: '',
            Summ: '',
            Date: '',
            NazPay: '',
            deal_id: '',
            status_id: '',
            Sch: '40702810470010330186',
            budget_item_id: '',
            errors:[],

        }
    },

    mounted() {
    },
    methods: {
        fortmatDate(date) {
            let options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            };
            if (date)
                return new Date(date).toLocaleString('ru', options);
            else return ''
        },
        showError(error) {
            if (error) {
                return `Поле должно быть заполнено! `
            } else return ''
        },
        showDanger(error) {
            if (error) {
                return 'alert alert-danger'
            } else return ''
        },
        fortmatDateShort(date) {
            let options = {
                day: 'numeric',
                month: 'long',
            };
            if (date)
                return new Date(date).toLocaleString('ru', options);
            else return ''
        },
        budgetItem(type) {
            if (type == 1) {
                return 'Нераспределенные выплаты'
            } else {
                return 'Нераспределенные поступления'

            }
        },
        makeMoney(n) {
            return parseFloat(n).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1 ") + ' р.';
        },
        makeCount(n) {
            return n.replace(/(\d)(?=(\d{3})+\.)/g, "$1 ");
        },
        typePlat(type) {
            if (type == 1) {
                return 'color: red;'
            } else {
                return 'color: green;'

            }
        },
        addRedact(id) {
            let check = document.getElementById('select_' + id);
            if (check.checked) {
                this.redactArray.push(id);
            } else {
                let index = this.redactArray.indexOf(id);
                if (index > -1) {
                    this.redactArray.splice(index, 1);
                }
            }

        },
        allChecked() {
            this.redactArray= []
            let transactions_date = this.transactions_date
             let all = document.getElementById('all');
            if (all.checked) {
                for (let i in transactions_date ) {

                    for (let j in transactions_date[i]) {
                        let check = document.getElementById('select_' + transactions_date[i][j].id);
                        check.checked = true;
                        this.redactArray.push(transactions_date[i][j].id);
                    }
                }
            }
            else {
                for (let i in transactions_date) {

                    for (let j in transactions_date[i]) {
                        let check = document.getElementById('select_' + transactions_date[i][j].id);
                        check.checked = false;
                        let index = this.redactArray.indexOf(transactions_date[i][j].id);
                        if (index > -1) {
                            this.redactArray.splice(index, 1);
                        }
                    }
                }
            }
        },
        redactArrayNotNull() {
            return this.redactArray.length > 0;
        },
        deleteTransaction() {
            console.log(this.redactArray);

            if (confirm('Точно удалить?')) {
                axios.post(`/api/delete`, {
                    redactArray: this.redactArray
                })
                    .then(res => {
                        this.getTransactions(res)
                    }

                )

            }
        },
        getTransactions(res)
        {
            let all = document.getElementById('all');
            all.checked = false;
            this.transactions_date = res.data
            for (let i = 0; i < this.redactArray.length; i++) {
                let check = document.getElementById('select_' + this.redactArray[i]);
                check.checked = false;
            }
            this.redactArray = []
        },

        copyTransaction() {
            axios.post(`api/copy`, {
                redactArray: this.redactArray
            })
                .then(res => {
                    this.getTransactions(res)
                })

        },
        createTransaction()
        {
            axios.post(`/api/create`, {
                Kontragent: this.Kontragent,
                Type: this.Type,
                Summ: this.Summ,
                Date: this.Date,
                NazPay: this.NazPay,
                deal_id: this.deal_id,
                status_id: this.status_id,
                Sch: this.Sch,
                budget_item_id: this.budget_item_id,

            })
                .then(res => {

                    this.transactions_date = res.data
                    this.add_show = false
                    this.Kontragent = ''
                    this.Type = ''
                    this.Summ = ''
                    this.Date = ''
                    this.NazPay = ''
                    this.deal_id = ''
                    this.status_id = ''
                    this.Sch = ''
                    this.budget_item_id = ''
                    let all = document.getElementById('all');
                    all.checked = false;
                    this.errors=[]

                },
                error => {
                    this.errors=error.response.data.errors
                }
                )
        }

    }
}



</script>
