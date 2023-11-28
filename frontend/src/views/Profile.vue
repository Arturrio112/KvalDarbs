<script setup>
  import axios from 'axios'
  import {ref, computed, onMounted, watchEffect} from 'vue'
  import { useRouter, useRoute } from 'vue-router';
  import MenuItem from '../components/MenuItem.vue'
  import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
  import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
  import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
  import Post from '../components/Post.vue'
  import Repost from '../components/Repost.vue'
  import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
  import Spider from 'vue-material-design-icons/Spider.vue';
  import Edit from 'vue-material-design-icons/AccountEdit.vue';
  import SearchBar from '../components/SearchBar.vue'
  import Close from 'vue-material-design-icons/Close.vue';
  const authUser= ref(null);
  const loading = ref(true)
  const userDataLoaded = ref(false);
  const router = useRouter();
  const route = useRoute();
  let activeSection = ref('posts')
  let userId;
  let profileId;
  const profile = ref(null)
  const posts = ref(null)
  const reposts = ref(null)
  let postsLoaded = ref(false)
  let repostsLoaded = ref(false)
  let openOptions = ref(false);
  let openEdit= ref(false)
  const editProfileErrors = ref([])
  const editedProfilePicture = ref(null); // Add this
  const editedProfileName = ref('');
  const fontColor = ref('');
  const borderColor = ref('')
  const profilePictureInput = ref(null);
  onMounted(()=>{
    const token = localStorage.getItem('authToken')
    if(!token){
      router.push({
        name: 'login'
      })
    }
    watchEffect(() => {
      profileId = route.params.id;
      console.log(profileId);

      axios.get(`http://localhost:8000/api/user-data`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res) => {
        console.log(res);
        if (res.data.data && res.data.data.profile) {
          authUser.value = res.data.data.profile;
          userId = authUser.value.id
          console.log(userId)
          console.log(authUser.value.id)
          userDataLoaded.value = true;
        } else {
          router.push({
            name: 'login'
          });
        }
      }).catch((err) => {
        console.error('Error fetching data', err);
        loading.value = false;
      });
      
      axios.get(`http://localhost:8000/api/profile/${profileId}`,  {
        params:{
          user_id: profileId
        },
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        profile.value = res.data.data.user
        fontColor.value = res.data.data.user.profile.fontColor ;
        borderColor.value = res.data.data.user.profile.borderColor ;
        console.log(res)
      }).catch((err)=>{
        console.error('Error fetching data', err);
      })
      axios.get(`http://localhost:8000/api/post/user/${profileId}`,{
        params:{
          user_id: profileId
        },
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        console.log(res)
        posts.value = res.data.data.posts
        postsLoaded.value=true
      }).catch((err)=>{
        console.error('Error fetching data', err);
      })
      axios.get(`http://localhost:8000/api/repost/user/${profileId}`, {
        params:{
          user_id: profileId
        },
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        console.log(res)
        reposts.value = res.data.data.reposts
        repostsLoaded.value = true
      }).catch((err)=>{
        console.error('Error fetching data', err);
      })
      if(profile.picture){
        axios.get('http://localhost:8000/api/file',{
            headers:{
                Authorization: `Bearer ${token}`,
            },
            params:{
                fileName: profile.picture
            }
        }).then((res)=>{
          console.log(res)
          profile.picture = res.data.data.media
        }).catch((err)=>{
            console.log(err);
        })
      }
    });

  })
  async function handleDelete() {
    const token = localStorage.getItem('authToken');
    console.log(userId)
    try {
      const response = await axios.delete(`http://localhost:8000/api/user/${userId}`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
      console.log(response);
      localStorage.removeItem('authToken');
      router.push({
        name: 'login'
      });
    } catch (error) {
      console.error('Error deleting user', error);
    }
  }
  let isFollowing = computed(() => {
    console.log(profile.value.followers)
    return profile.value.followers.some((followers) =>  followers ? followers.follower_id === userId : false);
  });
  async function handleFollow() {
    const token = localStorage.getItem('authToken');
    const requestData = {
        follower_id: userId,
        followed_id: profile.value.id,
    };
    console.log(profile.value.id)
    try {
        if (!isFollowing.value) {
            // Follow the user
            const response = await axios.post(`http://localhost:8000/api/user/${profileId}/follow`, requestData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });
            profile.value.followers.push(response.data.data.follow)
            profile.value.followers_count +=1
            console.log(response);
        } else {
            // Unfollow the user
            const response = await axios.delete(`http://localhost:8000/api/user/${profileId}/follow`, {
                data: requestData,
                headers: {
                    Authorization: `Bearer ${token}`,
                }
            });
            console.log(response);
            const followerIndex = profile.value.followers.findIndex(follower => follower.follower_id === userId);
            if (followerIndex !== -1) {
                profile.value.followers.splice(followerIndex, 1);
                profile.value.followers_count -=1
            }
        }
        
    } catch (error) {
        console.error('Error following/unfollowing user', error);
    }
  }
  const toggleOpenOptions = () => {
    openOptions.value = !openOptions.value;
  }
  const toggleEdit = ()=>{
    openOptions.value=false
    openEdit.value = !openEdit.value
  }
  function onProfilePictureChange() {
    editedProfilePicture.value = profilePictureInput.value.files[0];
  }
  const isAllowedType = (type)=>{
    const allowedTypes = ['jpeg', "jpg", 'png', 'gif'];

    const fileExtension = type.split('/')[1];

    return allowedTypes.includes(fileExtension);
  }
  const handleSubmit =  () =>{
    editProfileErrors.value = []
    handleEdit(profile.value.profile.nickname)
  }
  function handleEdit(nickname) {
    console.log(nickname)
    editProfileErrors.value = []
    const token = localStorage.getItem('authToken');
    const formData = new FormData();
    if (editedProfilePicture.value && !isAllowedType(editedProfilePicture.value.type)) {
      editProfileErrors.value.push('Added file format is not supported, supported file formats - jpeg, png, jpg, gif ');
      console.log('pic err')
    }
    if(editedProfilePicture.value ){
      formData.append('profilePicture', editedProfilePicture.value);
    }

    if (fontColor.value && !isValidHexColor(fontColor.value)) {
      editProfileErrors.value.push('Invalid Font color value');
      console.log('font err')
    }

    if (borderColor.value && !isValidHexColor(borderColor.value)) {
      editProfileErrors.value.push('Invalid Border color value');
      console.log('border err')
    }

    if (editedProfileName.value) {
      editedProfileName.value = editedProfileName.value.trim();
      if (editedProfileName.value === '') {
        editProfileErrors.value.push('Profile name cannot be empty');
        console.log('name err')
      }
    }
    if (editProfileErrors.value.length > 0) {
      // If there are errors, do not proceed with form submission
      editedProfilePicture.value = null
      fontColor.value = profile.value.profile.fontColor
      borderColor.value = profile.value.profile.borderColor
      editedProfileName.value = ''
      console.log('err occured')
      return;
    }
    console.log(fontColor.value)
    formData.append('profileName', editedProfileName.value || nickname);
    formData.append('borderColor', borderColor.value || profile.value.profile.borderColor);
    formData.append('fontColor',fontColor.value || profile.value.profile.fontColor)
    formData.append('userId', userId)
    formData.append('profileId', parseInt(profileId))
    console.log(userId, profileId)
    console.log(editedProfileName.value, editedProfilePicture.value)
     axios.post(`http://localhost:8000/api/edit-profile/${profileId}`, formData, {
        headers: {
          Authorization: `Bearer ${token}`
        },
      }).then((response)=>{
        console.log(response);
        openEdit.value = false;
        window.location.reload();
      }).catch((error) =>{
      console.error('Error editing profile', error);
    })
  }
  function isValidHexColor(color) {
    const hexColorRegex = /^#[0-9A-Fa-f]{6}$/;
    return hexColorRegex.test(color);
  }
</script>

<template>
  <div class="fixed w-full">
    <div class="max-w-[1400px] flex mx-auto">
      <div class="lg:w-3/12 w-[60px] h-[100vh] max-w-[350px] lg:px-4 lg:mx-auto">
        <div class="p-2 px-3 mb-4">
          <Spider fillColor="#fca521" :size="37"/>
        </div>
        <template v-if="userDataLoaded">
        <router-link :to="'/'">
          <MenuItem iconString="Home"/>
        </router-link>
        <router-link :to="'/explore'">
          <MenuItem iconString="Explore"/>
        </router-link>
        <router-link :to="'/messages'">
          <MenuItem iconString="Messages"/>
        </router-link>
        <router-link :to="`/user/${userId}`">
          <MenuItem iconString="Profile"/>
        </router-link>
        </template>
      </div>

      <div class="lg:w-7/12 w-11/12 border-x border-gray-800 relative">
        <div class=" bg-gray-300 bg-opacity-50 backdrop-blur-md z-10 absolute w-full">
          <div v-if="profile" class="border-gray-800 border-b w-full">
            <div class="w-full text-black text-[22px] font-extrabold p-4 flex">
              <router-link :to="'/'">
                <ArrowLeft fillColor="#000000" :size="28" class="rounded-full hover:bg-gray-300 transition duration-200 ease-in-out block cursor-pointer mr-2"/>
              </router-link>
              <div class="flex">
                <span v-if="profile.profile.fontColor" :style="{ color: isValidHexColor(profile.profile.fontColor) ? profile.profile.fontColor : '#000000' }">
                  {{profile.profile.nickname }}
                </span>
                <span v-else>
                  {{profile.profile.nickname }}
                </span>
                <CheckDecagram v-if="profile.profile.verified" fillColor="#fca521" :size="18"/>
              </div>
            </div>
            <div class="flex justify-between">
              <div class="flex items-center mt-2 mb-2 ml-2">
                <img
                  v-if="profile.profile.picture!=null"
                  :src="profile.profile.picture"
                  alt="Profile Picture"
                  class="w-[100px] h-[100px] rounded-full object-cover mr-2"
                  :style="{ borderColor: `${profile.profile.borderColor}`, borderWidth: '2px', borderStyle: 'solid' }"
                />
                <img
                  v-else
                  src="../assets/pic.png"
                  alt="Profile Picture"
                  class="w-[100px] h-[100px] rounded-full object-cover mr-2"
                />
                <div>
                  <div class="text-xl font-extrabold mt-4">
                    <div class="flex">
                      <span v-if="profile.profile.fontColor" :style="{ color: isValidHexColor(profile.profile.fontColor) ? profile.profile.fontColor : '#000000' }">
                        {{profile.profile.nickname }}
                      </span>
                      <span v-else>
                        {{profile.profile.nickname }}
                      </span>
                      <CheckDecagram v-if="profile.profile.verified" fillColor="#fca521" :size="18"/>
                    </div>
                  </div>
                  <div class="text-gray-600">@{{ profile.name }}</div>
                </div>
              </div>
              <div v-if="userId == profileId">
                <div  class="
                hover:bg-gray-400
                  rounded-full
                  cursor-pointer
                  relative
                  mr-2
                ">
                  <button type="button" class="block p-2">
                    <DotsHorizontal @click="toggleOpenOptions"/>
                  </button>
                    <div
                      v-if="openOptions"
                      class="
                        absolute
                        mt-1
                        p-3
                        right-0
                        w-[300px]
                      bg-black
                         border
                        border-gray-700
                        rounded-lg
                        shadow-lg
                    ">
                      <div
                        class="
                          flex
                          items-center
                          cursosr-pointer
                                "
                        @click="handleDelete"
                      >
                        <TrashCanOutline class="pr-3" fillColor="#DC2626" :size="18"/>
                        <span class="text-red-600 font-extrabold">Delete</span>
                      </div>
                      <div @click="toggleEdit" class="
                        flex
                        items-center
                        cursor-pointer
                      ">
                        <Edit class="pr-3" fillColor="white" :size="18"/>
                        <span class="font-extrabold text-white">Edit</span>
                      </div>
                  </div>
                </div>
              </div>
              <div v-else>
                <button
                    v-if="!isFollowing"
                    @click="handleFollow"
                    class="
                        bg-blue-500
                        text-white
                        font-semibold
                        py-2
                        px-4
                        rounded-full
                        hover:bg-blue-600
                        transition
                        duration-200
                        ease-in-out
                        mr-2
                    "
                >
                    Follow
                </button>
                <button
                    v-else
                    @click="handleFollow"
                    class="
                        bg-gray-500
                        text-white
                        font-semibold
                        py-2
                        px-4
                        rounded-full
                        hover:bg-red-600
                        transition
                        duration-200
                        ease-in-out
                        mr-2
                    "
                >
                    Followed
                </button>
              </div>
            </div>
            <div class="mr-2 ml-2 mb-2 flex justify-between">
              <div >
                <div class="text-center">
                  <div class="text-xl font-extrabold">Followers: {{ profile.followers_count }}</div>
                </div>
              </div>
              <div >
                <div class="text-center">
                  <div class="text-xl font-extrabold">Posts: {{ profile.posts_count }}</div>
                </div>
              </div>
              <div >
                <div class="text-center">
                  <div class="text-xl font-extrabold">Reposts: {{ profile.repost_count }}</div>
                </div>
              </div>
            </div>
            <div class="flex">
              <div
                class="
                  flex
                  items-center
                  justify-center
                  w-full
                  h-[60px]
                  text-black
                  text-[17px]
                  font-extrabold
                  p-4
                  hover:bg-gray-500
                  hover:bg-opacity-30
                  cursor-pointer
                  transition
                  duration-200
                  ease-in-out
                "
              >
                <div
                  @click="activeSection = 'posts'" 
                  :class="{ 'border-b-[#fca521]': activeSection === 'posts' }"  
                  class="
                  inline-block
                  text-center
                  border-b-4
                  border-b-gray
                  h-[60px]
                "
                >
                  <div class="my-auto mr-4 mt-4">Posts</div>
                </div>
              </div>
              
              <div
                class="
                  flex
                  items-center
                  justify-center
                  w-full
                  h-[60px]
                  text-black
                  text-[17px]
                  font-extrabold
                  p-4
                  hover:bg-gray-500
                  hover:bg-opacity-30
                  cursor-pointer
                  transition
                  duration-200
                  ease-in-out
                "
              >
                <div
                  @click="activeSection = 'reposts'" 
                  :class="{ 'border-b-[#fca521]': activeSection === 'reposts' }"  
                  class="
                  inline-block
                  text-center
                  border-b-4
                  border-b-gray
                  h-[60px]
                ">
                  <div class="my-auto ml-4 mt-4">Reposts</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="
          absolute
          top-0
          h-full
          overflow-y-scroll
          no-scrollbar
          w-full        
        "
        >
          <div v-if="postsLoaded && userDataLoaded && activeSection==='posts'">
            <div class="mt-[260px]"></div>
              <div  class="flex" v-for="post in posts" :key="post.id">
                <Post :post="post" :userId="userId"/>
            </div>
          </div>
          <div v-else>
            <div class="mt-[290px]"></div>
            <div  class="flex" v-for="repost in reposts" :key="repost.id">
                <Repost :repost="repost" :userId="userId"/>
            </div>
          </div>
          <div class="pb-4"></div>
        </div>
      </div>
      <div class="
        lg:block
        hidden
        lg:w-4/12
        h-screen
        border-l
        border-gray-800
        pl-4
      ">
        <SearchBar/>
      </div>
    </div>
    <div id="editOverlay" v-if="openEdit" class="fixed top-0 left-0 w-full h-screen z-20">
      <div class="fixed inset-0 flex items-center justify-center">
        <div class="bg-white w-96 p-6 rounded-lg shadow-lg">
          <div class="flex justify-between">
            <h2 class="text-2xl font-semibold mb-4">Edit Profile</h2>
            <Close fillColor="#000000" :size="28" class="cursor-pointer" @click="toggleEdit"/>
          </div>
          <div>
            <div class="mb-4">
              <label for="profilePicture" class="block text-gray-800 font-semibold">Profile Picture</label>
              <input
                type="file"
                id="profilePicture"
                ref="profilePictureInput" 
                accept="image/*"
                class="border-gray-300 rounded-lg p-2 w-full"
                @change="onProfilePictureChange" 
              />
            </div>
            <div class="mb-4">
              <label for="profileName" class="block text-gray-800 font-semibold">Profile Name</label>
              <input
                type="text"
                id="profileName"
                v-model="editedProfileName"
                class="border-gray-800 rounded-lg p-2 w-full bg-gray-300"
                :placeholder= "profile.profile.nickname" 
              />
            </div>
            <div class="mb-4">
              <label for="fontColor" class="block text-gray-800 font-semibold">Font color</label>
              <input
                type="color"
                id="fontColor"
                v-model="fontColor"
                class="border-gray-300 rounded-lg p-2 w-full cursor-pointer"
              />
            </div>
            <div class="mb-4">
              <label for="borderColor" class="block text-gray-800 font-semibold">Border color</label>
              <input
                type="color"
                id="borderColor"
                v-model="borderColor"
                class="border-gray-300 rounded-lg p-2 w-full cursor-pointer"
              />
            </div>
            <div class="text-right">
              <button @click="handleSubmit" class="bg-blue-500 text-white px-4 py-2 rounded-full">Save</button>
            </div>
            <div v-if="editProfileErrors.length > 0">
              <div v-for="error in editProfileErrors" :key="error" >
                <div class="text-rose-600">{{ error }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>