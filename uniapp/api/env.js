let BASE_URL
//开发环境中
if (process.env.NODE_ENV === 'development') {
  // 开发环境
  BASE_URL = 'http://jingshuiji.com'  //开发环境请求地址
} else {
  // 生产环境
  BASE_URL = 'http://jingshuiji.com'  //生成环境请求地址
}
 
export default BASE_URL