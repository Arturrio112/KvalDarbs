
<template>
    <div class="flex h-screen justify-center items-center bg-gray-100">
      <div class="bg-white p-8 rounded-lg shadow-md w-5/6">
        <h1 class="text-2xl font-bold mb-4 text-center">Create your account</h1>
        <form @submit="handleSubmit">
          <!-- Email Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="email">Email:</label>
            <input type="email" id="email" v-model="email" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div class="mb-4">
            <p class="text-rose-700" v-if="msg.email">{{ msg.email }}</p>
          </div>
          <!-- Nickname Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="nickname">Name:</label>
            <input type="text" id="nickname" v-model="nickname" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div class="mb-4">
            <p class="text-rose-700" v-if="msg.nickname">{{ msg.nickname }}</p>
          </div>
          <!-- Birthdate Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="birthdate">Birthdate:</label>
            <input type="date" id="birthdate" v-model="birthdate" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div class="mb-4">
            <p class="text-rose-700" v-if="msg.birthdate">{{ msg.birthdate }}</p>
          </div>
          <!-- Password Field -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="password">Password:</label>
            <input type="password" id="password" v-model="password" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div class="mb-4">
            <p class="text-rose-700" v-if="msg.password">{{ msg.password }}</p>
          </div>
          <!-- Confirm Password Field -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700" for="confirmPassword">Confirm password:</label>
            <input type="password" id="confirmPassword" v-model="confirmPassword" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div class="mb-4">
            <p class="text-rose-700" v-if="msg.confirmPassword">{{ msg.confirmPassword }}</p>
          </div>
          <!-- Submit Button -->
          <button type="submit" class="block mx-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring focus:border-blue-300 w-2/3">Signup</button>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios'
  export default {
    data() {
      return {
        email: '',
        nickname: '',
        birthdate: '',
        password: '',
        confirmPassword: '',
        msg: {
          email: '',
          nickname: '',
          birthdate: '',
          password: '',
          confirmPassword: ''
        }
      };
    },
    methods: {
      async handleSubmit(event) {
        event.preventDefault();
        const errorFields = Object.values(this.msg).filter((msg)=> msg !== '')
        if(errorFields.length === 0){
          const userData = {
            name: this.nickname,
            email: this.email,
            birthdate: this.birthdate,
            password: this.password,
            password_confirmation: this.confirmPassword
          }
         
          axios.post('http://localhost:8000/api/register', userData,{
            withCredentials: true,
            
          })
            .then((res)=>{
              console.log('User registration successful!', res.data);
              const token = res.data.data.token
              localStorage.setItem('authToken', token)
              this.$router.push({
                name: 'home'
              })
            }).catch((err)=>{
              console.log('User registration successful!', err);
          })
        }
      },
      validateEmail(v) {
        if (v === '') {
          this.msg.email = "Provide an email";
        } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v)) {
          this.msg.email = "Invalid email";
        } else {
          this.msg.email = '';
        }
      },
      validateBirthdate(v) {
        if (v === '') {
          this.msg.birthdate = "Enter your birthdate";
        } else {
          const birthDate = new Date(v);
          const thirteenYearsAgo = new Date();
          thirteenYearsAgo.setFullYear(thirteenYearsAgo.getFullYear() - 13);

          if (birthDate > thirteenYearsAgo) {
            this.msg.birthdate = "You must be at least 13 years old to register.";
          } else {
            this.msg.birthdate = '';
          }
        }
      },
      validateName(v){
        if (v === '') {
          this.msg.nickname = "Don't have a name?";
        } else if (v.length > 50) {
          this.msg.nickname = "Name is too long";
        } else {
          this.msg.nickname = '';
        }
      },
      validatePw(v) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/;

        if (!regex.test(v)) {
          this.msg.password = "Your password should be at least 10 characters long and use a mix of uppercase, lowercase, numbers, and symbols.";
        } else {
          this.msg.password = '';
        }
      },
      validateIfPwsMatch(v1, v2) {
        if (v1 !== v2) {
          this.msg.confirmPassword = "Both password fields need to match";
        } else {
          this.msg.confirmPassword = "";
        }
      }
    },
    watch: {
      email(value) {
        this.email = value;
        this.validateEmail(value);
      },
      nickname(value) {
        this.nickname = value;
        this.validateName(value)
      },
      birthdate(value) {
        this.birthdate = value;
        this.validateBirthdate(value);
      },
      password(value) {
        this.password = value;
        this.validatePw(value);
        this.validateIfPwsMatch(this.confirmPassword, value);
      },
      confirmPassword(value) {
        this.confirmPassword = value;
        this.validateIfPwsMatch(value, this.password);
      }
    }
  };
</script>