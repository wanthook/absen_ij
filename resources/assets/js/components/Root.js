import React, { Component } from 'react'

import Header from './Header';
import Footer from './Footer';
import SideBar from './SideBar';

export default class Root extends Component {
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
