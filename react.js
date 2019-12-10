// ReactJS/Redux code sample with basic structure using Mui CSS for presentation.
// Code includes main component and two secondary components, categories and attributes.
// A d3 graph is included inside the categories component."

// ------------------------------------------------------------------------
// Main App component
// ------------------------------------------------------------------------
import React, { Component } from 'react';
import Container from 'muicss/lib/react/container';
import Row from 'muicss/lib/react/row';
import Col from 'muicss/lib/react/col';
import Category from './components/category';

class App extends Component {
  render() {
    return (
      <Container fluid={true}>
        <Row>
          <Col xs="12">
            <Category />
          </Col>
        </Row>
      </Container>
    );
  }
};

export default App;

// ------------------------------------------------------------------------
// Category component
// ------------------------------------------------------------------------
import React, { Component } from 'react';
import { connect } from 'react-redux';
import Tab from 'muicss/lib/react/tab';
import Tabs from 'muicss/lib/react/tabs';
import Attributes from '../attribute/attributes';

class Category extends Component {
  componentDidMount() {

    // Base data for the donut chart
    const dataset = [{
      label: 'Orientation',
      count: 1
    }, {
      label: 'Training',
      count: 1
    }, {
      label: 'Reward',
      count: 1
    }, {
      label: 'Restaurant',
      count: 1
    }, {
      label: 'Culture',
      count: 1
    }, {
      label: 'Attraction',
      count: 1
    }, {
      label: 'Interviewing',
      count: 1
    }];

    let width = 460; // donut width
    let height = 460; // donut height
    let radius = 380 / 2; // donut radius
    let innerRadius = 90;  // donut inner radius
    let circleRadius = 22; // inner circle radius
    let mainGTranslate = 'translate(' + width/2 + ', ' + height/2 + ')'; // main svg location

    // Inner circles location's and fill color's inside the donut inner radius
    let circleLocations = [{
      x: -55,
      y: 0,
      fill: '#00BDF1'
    }, {
      x: -30,
      y: -45,
      fill: '#00BDF1'
    }, {
      x: -30,
      y: 45,
      fill: '#00BDF1'
    }, {
      x: 55,
      y: 0,
      fill: '#9B508C'
    }, {
      x: 30,
      y: -45,
      fill: '#9B508C'
    }, {
      x: 30,
      y: 45,
      fill: '#9B508C'
    }];

    // Color scale for the donut sections
    const color = d3
      .scaleOrdinal()
      .range(['#DA291C', '#DA291C', '#0288D1', '#00B4D5', '#54CACA', '#FFD35B', '#FBAD3F']);

    // Starting to draw the chart, first we create the svg
    const svg = d3
      .select('#graph')
      .append('svg')
      .attr('width', width)
      .attr('height', height)
      .append('g')
      .attr('transform', mainGTranslate);

    // Creating the base arc based on the main radius and the the main inner radius with some paddign between each arc
    const arc = d3
      .arc()
      .innerRadius(innerRadius)
      .outerRadius(radius)
      .padAngle(0.01);

    // Compute the necessary angles to represent a tabular dataset as a pie or donut chart
    const pie = d3
      .pie()
      .value((d) => d.count)
      .sort(null);

    // Adding path's for each dataset item using the pie generator created before
    const arcs = svg
      .selectAll('path')
      .data(pie(dataset))
      .enter()
      .append('g')
      .attr('class', (d, i) => `slice ${i}`);

    // Adding the fill color and assigning each arc with the arc object created before
    arcs
      .append('svg:path')
      .attr('fill', (d) => color(d.data.label))
      .attr('d', arc);

    // Adding the nodes for the labels on each arc of the donut and centering
    const text = arcs
      .append('svg:text')
      .attr('transform', (d) => {
        const temp = d;
        temp.innerRadius = innerRadius;
        temp.outerRadius = radius;
        const result = `translate(${arc.centroid(temp)})`;

        return result;
      })
      .attr('text-anchor', 'middle');

    // Adding the label to each node created before
    text
      .append('tspan')
      .attr('dy', 0)
      .attr('x', 0)
      .text((d, i) => dataset[i].label);

    // Adding all inner circles to the svg
    $.each(circleLocations, (i, item) => {
      svg
        .append('circle')
        .attr('cx', item.x)
        .attr('cy', item.y)
        .attr('r', circleRadius)
        .attr('fill', item.fill);
    });

    // Adding the inner text with format
    svg
      .append('text')
      .attr('x', -18)
      .attr('y', -4)
      .text('VISION')
      .attr('color', '#F5C400')
      .attr('font-family', 'Arial')
      .attr('font-size', '0.75rem')
      .attr('font-weight', 'bold');

    // Adding the inner text with format
    svg
      .append('text')
      .attr('x', -12)
      .attr('y', 16)
      .text('2020')
      .attr('color', '#F5C400')
      .attr('font-family', 'Arial')
      .attr('font-size', '0.75rem')
      .attr('font-weight', 'bold');
  }

  getTabs(tabs) {
    return tabs.map((category) => {
      return (
        <Tab key={category} value={category} label={category}>
          <Attributes category = {category}/>
            </Tab>
      );
    });
  }

  render() {
    return (
      <div>
        <div id=\"graph\"></div>

        <Tabs onChange={this.onChange} defaultSelectedIndex={0} justified={true}>
          { this.getTabs(this.props.categories) }
        </Tabs>
      </div>
    );
  }
};

function mapStateToProps(state) {
  return {
    categories: state.categories
  };
}

export default connect(mapStateToProps)(Category);


// ------------------------------------------------------------------------
// Attributes Component
// ------------------------------------------------------------------------
import React, {Component} from 'react';
import {connect} from 'react-redux';
import PropTypes from 'prop-types';
import { bindActionCreators } from 'redux';
import Col from 'muicss/lib/react/col';
import Row from 'muicss/lib/react/row';
import Button from 'muicss/lib/react/button';
import Actions  from '../../actions/index';

let attributeId = 1;

class Attributes extends Component {
  handleAddAttribute() {
    const defaultAttribute = {
      id: attributeId++,
      name: '',
      description: '',
      category: this.props.category
    };

    this.props.addAttribute(defaultAttribute);
  }

  render() {
    const attributesJSON = JSON.stringify(this.props.attributes, null, 4);
      return (
        <Row>
          <Col xs={12}>
            <Button color=\"primary\" onClick={this.handleAddAttribute.bind(this)}>Add Attribute</Button>
          </Col>
        </Row>
      );
    }
};

Attributes.propTypes = {
  category: PropTypes.string.isRequired
};

function matchDispatchToProps(dispatch) {
  return bindActionCreators({
    addAttribute: Actions.addAttribute,
    deleteAttribute: Actions.deleteAttribute
  }, dispatch);
}

function mapStateToProps(state) {
  return {
    attributes: state.attributes
  };
}

export default connect(mapStateToProps, matchDispatchToProps)(Attributes);

// ------------------------------------------------------------------------
// Actions
// ------------------------------------------------------------------------
const Actions = {
  addAttribute: (attribute) => {
    return {
      type: "ADD_ATTRIBUTE",
      payload: attribute
    };
  },
  updateAttribute: (attribute) => {
    return {
      type: "UPDATE_ATTRIBUTE",
      payload: attribute
    };
  },
  deleteAttribute: (attribute) => {
    return {
      type: "DELETE_ATTRIBUTE",
      payload: attribute
    };
  }
};

export default Actions;

// ------------------------------------------------------------------------
// Reducer
// ------------------------------------------------------------------------
export default (state = [], action) => {
  switch (action.type) {

    case 'ADD_ATTRIBUTE':
      return [...state, action.payload]

    case 'UPDATE_ATTRIBUTE':
      return state.map((attribute) => {
        return attribute.id === action.payload.id ?  action.payload : attribute
      });

    case 'DELETE_ATTRIBUTE':
      return state.filter((attribute) => {
        return (attribute.id !== action.payload.id)
      });

    default:
      return state;
    }
}
