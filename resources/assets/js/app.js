import React, { Component } from 'react'
import ReactDOM from 'react-dom';

import Header from './components/Header';
import Footer from './components/Footer';
import SideBar from './components/SideBar';

export default class App extends Component {
  render() {
    return (
      <div>
        <Header />
        <Footer />
        <SideBar />
      </div>
    )
  }
}
// render(<App/>, window.document.getElementById('root'));
if (document.getElementById('root')) {
  ReactDOM.render(<App />, document.getElementById('root'));
}