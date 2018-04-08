<template>
  <div id="welcome" class="container">
    <div class="header">
        <h3>Happy Birthday</h3>
        <h1>NOEY BNK48</h1>
        <small>คำอวยพรทั้งหมด {{ total }} ข้อความ</small>
    </div>
    <div class="cake" :style="{ cursor: success ? 'default' : 'pointer' }" @click="openModal">
      <transition name="fade">
        <div class="candle" v-if="success">
          <div class="flame"></div>
        </div>
      </transition>
      <div class="strawberry">
        <div class="seeds">
          <div class="seeds one"></div>
          <div class="seeds two"></div>
          <div class="seeds three"></div>
          <div class="seeds four"></div>
          <div class="seeds five"></div>
          <div class="seeds six"></div>
        </div>
      </div>
      <div class="stem">
        <div class="stem top"></div>
        <div class="stem middle"></div>
        <div class="stem bottom"></div>
      </div>
      <div class="fill">
        <div class="fill top">
          <div class="fill strawberry-fill"></div>
          <div class="fill strawberry-fill right"></div>
        </div>
        <div class="fill middle">
          <div class="fill strawberry-fill"></div>
          <div class="fill strawberry-fill right"></div>
        </div>
        <div class="fill bottom"></div>
      </div>
    </div>
    <div class="bottom" :style="{ marginBottom: success ? '300px' : 0 }">
      <div class="hint" v-if="!success">
        <small>คลิ๊กที่เค้กเพื่อร่วมอวยพร</small>
      </div>
      <transition name="fade-2">
        <!-- <div class="card" v-if="success"> -->
        <div class="card-container">
          <div class="card" v-for="(item, index) in message" :key="index">
            <div class="card-body">
              <blockquote class="blockquote">
                <p class="mb-0" v-html="escapeHTML(item.message)"></p>
                <footer class="blockquote-footer card-name">💚 🐸 {{item.name}}</footer>
              </blockquote>
            </div>
          </div>
        </div>
      </transition>
      <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalTitle">การ์ดอวยพร</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form>
                <div class="form-group">
                  <label for="name">ชื่อ <small>({{ modal.name.length }} / {{ rules.name.max }})</small></label>
                  <input type="text" class="form-control" id="name" aria-describedby="nameHelp" placeholder="ชื่อ" v-model.trim="modal.name" @input="$v.modal.name.$touch()">
                  <small id="nameHelp" class="form-text text-danger" v-if="isError('name', 'required')">จำเป็นต้องระบุ</small>
                  <small id="nameHelp" class="form-text text-danger" v-if="isError('name', 'length')">กรุณาระบุ {{ rules.name.min }}-{{ rules.name.max }} ตัวอักษร</small>
                </div>
                <div class="form-group">
                  <label for="message">ข้อความ <small>({{ modal.message.length }} / {{ rules.message.max }})</small></label>
                  <textarea class="form-control" id="message" aria-describedby="messageHelp" placeholder="ข้อความ" v-model.trim="modal.message" @input="$v.modal.message.$touch()"></textarea>
                  <small id="messageHelp" class="form-text text-danger" v-if="isError('message', 'required')">จำเป็นต้องระบุ</small>
                  <small id="messageHelp" class="form-text text-danger" v-if="isError('message', 'length')">กรุณาระบุ {{ rules.message.min }}-{{ rules.message.max }} ตัวอักษร</small>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
              <button type="button" class="btn btn-primary" :disabled="invalidForm" @click="submit">อวยพร</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { required } from 'vuelidate/lib/validators'
  import numeral from 'numeral'
  export default {
    data () {
      return {
        total: 0,
        modal: {
          name: '',
          message: ''
        },
        card: {
          name: '',
          message: ''
        },
        message: {
        },
        rules: {
          name: {
            min: 1,
            max: 50
          },
          message: {
            min: 3,
            max: 300
          }
        }
      }
    },
    validations: {
      modal: {
        name: {
          required,
          length () {
            return this.modal.name.length >= this.rules.name.min && this.modal.name.length <= this.rules.name.max
          }
        },
        message: {
          required,
          length () {
            return this.modal.message.length >= this.rules.message.min && this.modal.message.length <= this.rules.message.max
          }
        }
      }
    },
    computed: {
      invalidForm () {
        return this.$v.modal.name.$invalid || this.$v.modal.message.$invalid
      },
      success () {
        return this.card.name && this.card.message
      }
    },
    methods: {
      getTotal () {
        axios.get('/api/get-total').then((res) => {
          let { total } = res.data
          this.total = numeral(total).format('0,0')
        })
      },
      getMessage () {
        axios.get('/api/get-all-message').then((res) => {
          let { message } = res.data
          this.message = message;
        })
      },
      openModal () {
        if (this.success) {
          return false
        } else {
          $('#modal').modal('show')
        }
      },
      isError (model, validator) {
        return this.$v.modal[model].$error ? this.$v.modal[model][validator] === false : false
      },
      submit () {
        if (!this.invalidForm) {
          axios.post('/api/message', this.modal).then((res) => {
            let { result, total } = res.data
            let { name, message } = result
            this.message.unshift(result);
            this.total = numeral(total).format('0,0')
            this.card.name = name
            this.card.message = message;

            $('#modal').modal('hide')
          })
        }
      },
      escapeHTML (message) {
        return message
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;')
          .replace(/\n/g, '<br/>')
      }
    },
    created () {
      this.getTotal()
      this.getMessage();
    },
    mounted () {
      console.log('Component mounted.')
      $('#modal').on('hidden.bs.modal', () => {
        this.modal.name = ''
        this.modal.message = ''
        this.$v.modal.name.$reset()
        this.$v.modal.message.$reset()
      })
    }
  }
</script>

<style type="text/css" lang="sass">
  @import '../../../assets/sass/welcome.scss'
</style>