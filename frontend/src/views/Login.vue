<script>
  import axios from 'axios'
  export default {
    data() {
      return {
        email: '',
        password: '',
        loginError: ''
      };
    },
    created() {
      this.deleteAuthToken();
    },
    methods: {
      //Idzēš autentifikācijas talonu, ja tas eksistē 
      async deleteAuthToken() {
        const token = localStorage.getItem('authToken');
        if (token) {
          axios.post('http://localhost:8000/api/logout', {}, {
            headers: {
              Authorization: `Bearer ${token}`,
            },
            withCredentials: true,
          })
          .then(() => {
            console.log('logged out')
            localStorage.removeItem('authToken');
          })
          .catch((error) => {
            console.error('Error logging out:', error);
          })
        }
      },
      //Funkcija, kas nosūta pieprasījumu, lai pieslēgtos sistēmai
      async handleSubmit(event) {
        event.preventDefault();
          const userData = {
            email: this.email,
            password: this.password,
          }
          axios.post('http://localhost:8000/api/login', userData,{
            withCredentials: true,
          })
            .then((res)=>{
              console.log('User loged in successful!', res);
              const token = res.data.data.token
              console.log(token)
              localStorage.setItem('authToken', token)
              this.$router.push({
                name: 'home'
              })
            }).catch((err)=>{
              console.log('Something went wrong', err);
              this.loginError = 'Email or password is incorrect'
          })
      },
    }
  };
</script>

<template>
  <div class="flex h-screen justify-center items-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-5/6 max-w-[1000px]">
      <h1 class="text-2xl font-bold mb-4 text-center">Sign in to SocNet</h1>
      <form @submit="handleSubmit">
        <!-- Email Field -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700" for="email">Email:</label>
          <input type="email" id="email" v-model="email" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <!-- Password Field -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700" for="password">Password:</label>
          <input type="password" id="password" v-model="password" required class="mt-1 px-4 py-2 w-full border rounded-md focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <!-- Submit Button -->
        <button type="submit" class="mb-4 block mx-auto px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring focus:border-blue-300 w-2/3">Sign in</button>
        <div class="mt-4 mb-4" v-if="loginError">
          <p class="text-red-500">{{ loginError }}</p>
        </div>
        <div class="mb-4">
          <p class="text-slate-400">Don't have an account? <router-link to="/signup" class="text-blue-500 underline">Sign up</router-link></p>
        </div>
      </form>
    </div>
  </div>
</template>
